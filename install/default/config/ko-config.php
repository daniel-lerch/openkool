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

//Titel
$HTML_TITLE= '';

//Module
$MODULES = array();

//Languages
$WEB_LANGS = array();
$GET_LANG_FROM_BROWSER = FALSE;

//Datenbank
$mysql_user = '';
$mysql_pass = '';
$mysql_server = '';
$mysql_db = '';

//LDAP
$ldap_enabled = FALSE;
/*
$ldap_admin = 'kOOL_ldapadmin';
$ldap_admin_pw = '';
$ldap_server = 'your.ldap.server';
$ldap_dn = 'ou=kOOL_,dc=your,dc=ldap,dc=server';
*/
//Set to TRUE if all kOOL logins should get exported to LDAP, not only those with access to addresses
@define('LDAP_EXPORT_ALL_LOGINS', FALSE);

//Base
$BASE_URL  = '';
$BASE_PATH = '';
@define('FORCE_SSL', FALSE);

//Smarty
$smarty_dir = $BASE_PATH;

//Include path for smarty (if installed locally)
$INCLUDE_PATH_SMARTY = '';

//Transport settings for email (using SwiftMailer)
//method can be: smtp, mail or sendmail
//$MAIL_TRANSPORT = array('method' => 'smtp', 'host' => '', 'port' => '', 'ssl' => FALSE, 'tls' => FALSE, 'auth_user' => '', 'auth_pass' => '');

//SMS-Parameter
$SMS_PARAMETER = array('provider' => '', 'user' => '', 'pass' => '');

//Settings for mailing module
//$MAILING_PARAMETER = array('host' => '', 'port' => 110, 'user' => '', 'pass' => '', 'domain' => '', 'ssl' => false, 'validate-cert' => false, 'folder' => 'INBOX', 'return_path' => '', 'set_bulk_header' => false);

//Plugins
$PLUGINS = array(
);

//Warranty
@define('WARRANTY_GIVER', '');
@define('WARRANTY_EMAIL', '');
@define('WARRANTY_URL', '');

//Family sort order
$FAMFUNCTION_SORT_ORDER = array('husband' => 1, 'wife' => 2, 'child' => 3);

//Encryption key for get.php (as set in TYPO3 extension manager for kool_base)
@define('KOOL_ENCRYPTION_KEY', '');

//Allow SingleSignon through TYPO3 extension (kool_sso)
@define('ALLOW_SSO', FALSE);

//Set additional email fields from DB.ko_leute ('email' is set by default)
//$LEUTE_EMAIL_FIELDS[] = '';

//Set additional mobile fields from DB.ko_leute ('natel' is set by default)
//$LEUTE_MOBILE_FIELDS[] = '';

//Only force groups for persons when adding new records (not when editing)
@define('LEUTE_ADMIN_GROUPS_NEW_ONLY', FALSE);

//Session lifetime (in seconds)
@define('SESSION_TIMEOUT', 7200);
@define('SESSION_TIMEOUT_WARNING', TRUE);
@define('SESSION_TIMEOUT_AUTO_LOGOUT', FALSE);
//Save path for php session files. If not set the system's default path will be used
//@define('SESSION_SAVE_PATH', '');

//Allow group members to be exported to ezmlm mailinglists
@define('EXPORT2EZMLM', FALSE);

//Set Return-Path Header for sent emails. Disable if used email server doesn't allow to set Return-Path header.
@define('EMAIL_SET_RETURN_PATH', TRUE);

//iCal URL if different from BASE_URL (e.g. to use with separate subdomain without SSL if encryption is not supported by clients)
//$ICAL_URL = '';
//$RESICAL_URL = '';

//Define other rectypes as mapping arrays. E.g. for business address fields to be used in exports
//$RECTYPES['b'] = array('adresse' => 'b_adresse', 'plz' => 'b_plz', 'ort' => 'b_ort');
// Use _default to overwrite values for default rectype, e.g. empty firm field:
// $RECTYPES['_default'] = array('firm' => '');

//Define different colors for single events/reservations (see inc/ko.inc for details)
//$EVENT_COLOR = array();
//$RES_COLOR = array();

//Conditions under which leute_admin_spalten are not to be applied
//E.g. array('ALL' => array('dontapply' => '!_import_id'));
//ALL is 'ALL' or login id, command 'dontapply' for addresses where _import_id != ''
//Multiple conditions possible, separated by comma. They are linked with AND
//$LEUTE_ADMIN_SPALTEN_CONDITION = array();

// This option (if set to true) disables the whole family functionality of kOOL
$LEUTE_NO_FAMILY = FALSE;

// this option can be set to true in order to add the header 'Precedence: bulk' to outgoing group mails
//$MAILING_ENABLE_BULK_HEADER = false;

?>
