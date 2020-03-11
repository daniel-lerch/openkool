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

// return warning if called from console
if (isset($argc) && $argc >= 1) {
	if ($argv[1] == '-t') {
		$mail_id_in = trim($argv[2]);
		$recipient_in = trim($argv[3]);
		if (!$mail_id_in || !$recipient_in || !is_numeric($mail_id_in)) {
			die("This script can only be called from console for testing purposes. Usage >> php mailing.php -t <mail_id> <recipient_mail|recipient_id> \n");
		} else {
			$ko_path = './';
			require_once('inc/ko.inc');
			print (ko_mailing_main (true, $mail_id_in, $recipient_in)."\n");
			exit;
		}
	} else {
		die("This script can only be called from console for testing purposes. Usage >> php mailing.php -t <mail_id> <recipient_mail|recipient_id> \n");
	}
}

// Constants: Mailing status
define('MAILING_STATUS_OPEN', 1);
define('MAILING_STATUS_CONFIRMED', 2);
define('MAILING_STATUS_SENT', 3);

define('MAILING_MAX_RECIPIENTS_FOR_SUMMARY', 20);

//Contants: Errors
define('MAILING_ERROR_INVALID_GROUP_ID', 1);
define('MAILING_ERROR_INVALID_SMALLGROUP_ID', 2);
define('MAILING_ERROR_INVALID_SENDER', 3);
define('MAILING_ERROR_INVALID_CODE', 4);
define('MAILING_ERROR_NO_ALIAS_FOUND', 5);
define('MAILING_ERROR_INVALID_RECIPIENT', 6);
define('MAILING_ERROR_NON_UNIQUE_ALIAS', 7);
define('MAILING_ERROR_TOO_MANY_RECIPIENTS', 8);
define('MAILING_ERROR_NO_ACCESS', 9);
define('MAILING_ERROR_GROUP_NO_ACCESS', 10);
define('MAILING_ERROR_SMALLGROUP_NO_ACCESS', 11);
define('MAILING_ERROR_MYLIST_NO_ACCESS', 12);
define('MAILING_ERROR_ONLY_ALIAS', 13);
define('MAILING_ERROR_LEUTE_NO_ACCESS', 14);
define('MAILING_ERROR_CODE_ALREADY_CONFIRMED', 15);
define('MAILING_ERROR_GROUP_NO_ACCESS_EMAIL', 16);
define('MAILING_ERROR_MODERATION_EMAIL', 17);
define('MAILING_ERROR_MYLIST_EMPTY', 18);
define('MAILING_ERROR_FILTER_EMPTY', 19);
define('MAILING_ERROR_INVALID_GROUP_ROLE_ID', 20);
define('MAILING_ERROR_NO_RECIPIENTS', 21);
define('MAILING_ERROR_CRM_NO_USER', 22);
define('MAILING_ERROR_CRM_NO_ACCESS', 23);
define('MAILING_ERROR_CRM_NO_PROJECT', 24);
define('MAILING_ERROR_CRM_PROJECT_NO_ACCESS', 25);
define('MAILING_ERROR_BCC_HINT', 26);

function ko_mailing_main ($test = false, $mail_id_in = null, $recipient_in = null) {

	global $MAILING_PARAMETER,$BASE_PATH,$domain,$edit_base_link,$done_error_mails,$return_path,$imap,$max_recipients,$ko_menu_akt,$access;

	error_reporting(E_ALL);
	define ('CRLF', "\r\n");


	//Get ko_path from server settings
	$ko_path = $BASE_PATH;

	if(isset($_POST['GLOBALS']) || isset($_GET['GLOBALS'])) {
		ko_log('mailing_error', 'You cannot set the GLOBALS-array from outside this script.');
		return;
	}

	$ko_menu_akt = 'mailing';

	//Basic checks
	if(defined('ALLOW_SEND_EMAIL') && ALLOW_SEND_EMAIL === FALSE) return;
	if(!is_array($MAILING_PARAMETER) || sizeof($MAILING_PARAMETER) < 3) return;

	//Get mailing parameters from ko-config and ko_settings
	$host           = $MAILING_PARAMETER['host'];
	$port           = $MAILING_PARAMETER['port'];
	$user           = $MAILING_PARAMETER['user'];
	$pass           = $MAILING_PARAMETER['pass'];
	$domain         = $MAILING_PARAMETER['domain'];
	$ssl            = $MAILING_PARAMETER['ssl'];
	$cert           = $MAILING_PARAMETER['validate-cert'];
	$folder         = $MAILING_PARAMETER['folder'];
	$edit_base_link = $MAILING_PARAMETER['edit_base_link'];
	$bulk_header    = $MAILING_PARAMETER['set_bulk_header'];

	//Number of email to be sent by cycle
	$mails_per_cycle = ko_get_setting('mailing_mails_per_cycle');
	if($mails_per_cycle < 1) $mails_per_cycle = 30;
	if($mails_per_cycle > 100) $mails_per_cycle = 100;
	//Maximum number of recipients
	$max_recipients = ko_get_setting('mailing_max_recipients');
	if($max_recipients == '') $max_recipients = 0;

	//Set Return-Path for sent emails
	if(check_email($MAILING_PARAMETER['return_path'])) {
		$return_path = '-f'.$MAILING_PARAMETER['return_path'];
	} else if($MAILING_PARAMETER['return_path'] == 'USER') {
		$return_path = 'USER';
	} else if(defined('EMAIL_SET_RETURN_PATH') && EMAIL_SET_RETURN_PATH == TRUE) {
		$return_path = '-f'.ko_get_setting('info_email');
	} else {
		$return_path = '';
	}

	require($ko_path . 'inc/class.rawSmtpMailer.php');
	$mailer = new RawSmtpMailer(true);

	/** TESTING
	 * Allows to send a stored mailing email to a specified recipient
	 * Call with php mailing.php -t ID RECIPIENT
	 * Where ID is the ID of the mailing email from DB table ko_mailing_mails and RECIPIENT is the email address to send this email to
	 */
	if($test) {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING & ~E_NOTICE);
		
		$mail_id = $mail_id_in;
		$recipient = $recipient_in;

		if (is_numeric($recipient)) {
			$rec = db_select_data('ko_leute', "WHERE `id` = '{$recipient}'", '*', '', '', TRUE, TRUE);
			if (!$rec ) {
				return "ERROR: Invalid recipient ID: {$recipient}.\n";
			}
			$recipient = $rec ['email'];
			$recipient_id = $rec ['id'];
		} else {
			$rec = FALSE;
			$recipient_id = 1;
		}

		if(!$mail_id || !$recipient || !check_email($recipient)) {
			return "ERROR: Invalid mailID or recipient.\nCall as follows: php5 mailing.php test ID RECIPIENT.\n";
		}

		$mail = db_select_data('ko_mailing_mails', "WHERE `id` = '$mail_id'", '*', '', '', TRUE);

		if($mail_id != $mail['id'] || $mail['id'] <= 0) {
			return "ERROR: Invalid mailID. Could not find any mailing with the given ID.\n";
		}

		//Find quoted-printable in header
		if(FALSE !== strpos(strtolower($mail['header']), 'content-transfer-encoding: quoted-printable')) $qp = TRUE;
		else $qp = FALSE;
		//Find utf-8 encoding. If set then encode recipient's name
		//if(FALSE !== strpos(strtolower($mail['header']), 'charset=utf-8')) $utf8 = TRUE;
		//else $utf8 = FALSE;

		$to = "To: ".mb_encode_mimeheader($rec['name'], 'UTF-8', 'Q')." <".$recipient.">" . CRLF;

		$subject = "Subject: " . ko_mailing_markers($mail['subject'], $recipient_id, $recipient) . CRLF;
		$message = ko_emailtext(trim($mail['header'])).$to.$subject.CRLF.ko_emailtext(ko_mailing_markers($mail['body'], $recipient_id, $recipient, $qp));

		$mailer->removeAddresses();

		try {
			$mailer->setSender($mail['from']);
			$mailer->addAddress($recipient);
			$mailer->setMessage($message);
			$mailer->send();
		} catch (Exception $e) {
			return('ERROR: mailing_smtp_error: '. $e->getMessage());
		}

		return "okay";
	}//if(TEST)



	//Create POP3 connection
	$ssl = ($ssl==true) ? '/ssl' : '';
	$cert = ($cert==true) ? '' : '/novalidate-cert';
	$folder = $folder ? $folder : 'INBOX';
	$imap = imap_open('{'."$host:$port/pop3$ssl$cert"."}$folder",$user,$pass);

	//Exit if connection to IMAP failed
	if($imap == FALSE) {
		db_insert_data('ko_log', array('type' => 'mailing_error', 'comment' => getLL('mailing_error_imap'), 'date' => date('Y-m-d H:i:s')));
		return;
	}

	$done_error_mails = array();

	// try to extract BCC recipients for these address prefixes:
	$bcc_prefixes = array('crm');

	// handler function for mails
	// each handler function is called for each message, regardless of the return value of previous handlers
	// if all handlers return false, an error is reported (as email reply)
	$mail_handlers = array(
		'ko_mailing_handle_mail_crm',
		'ko_mailing_handle_mail_group',
	);


	//Get emails from pop account
	$imap_status = imap_check($imap);
	$num_mails = $imap_status->Nmsgs;
	if($num_mails > 0) {
		//Get mails
		$mails = array();
		$response = imap_fetch_overview($imap,'1:'.$num_mails);
		foreach ($response as $msg) $mails[$msg->msgno] = (array)$msg;

		//Group mail receivers by message_id
		//If one mail has two or more recipients, the same mail will be stored multiple times, so we group all receivers by message_id
		//For each mail all recipients will be handled below, so no need to work through all copies
		$unique_mails = array();
		foreach($mails as $mail) {
			$rawheader = imap_fetchheader($imap, $mail['msgno']);

			if(!isset($unique_mails[$mail['message_id']])) {
				//Get all recipients of this email
				$header = imap_rfc822_parse_headers($rawheader);
				$mail_recipients = array();
				foreach($header->to as $obj) {
					$mail_recipients['to'][] = format_userinput($obj->mailbox.'@'.$obj->host, 'email');
				}
				foreach($header->cc as $obj) {
					$mail_recipients['cc'][] = format_userinput($obj->mailbox.'@'.$obj->host, 'email');
				}
				$unique_mails[$mail['message_id']] = array(
					'mail' => $mail,
					'recipients' => $mail_recipients,
				);
			} else {
				$mail_recipients = $unique_mails[$mail['message_id']];
			}

			// try to resolve bcc receiver address from bcc header
			foreach(preg_split("/\\n(?!\\s)/",$rawheader) as $headerLine) {
				list($name,$value) = explode(':',$headerLine,2);
				if(in_array(strtolower($name),array('envelope-to','x-envelope-to','delivered-to','x-delivered-to'))) {
					foreach($bcc_prefixes as $prefix) {
						if(preg_match('/^([0-9a-z]+-)?('.preg_quote($prefix).'([^@]+)@'.preg_quote($domain).')$/i',trim($value),$matches)) {
							$mail_address = $matches[2];
							foreach($unique_mails[$mail['message_id']]['recipients'] as $recipients) {
								if(in_array($mail_address,$recipients)) {
									continue 2;
								}
							}
							$unique_mails[$mail['message_id']]['recipients']['bcc'][] = format_userinput($mail_address, 'email');
						}
					}
				}
			}
		}

		foreach($unique_mails as $unique_mail) {
			$mail = $unique_mail['mail'];
			$mail_recipients = $unique_mail['recipients'];

			//Check sender email and find corresponding kOOL login
			$access = array();
			$login_id = ko_mailing_get_sender_login($mail['from']);
			if($login_id) {
				ko_get_login($login_id,$login);
			} else {
				$login = null;
			}

			$handeled = false;
			foreach($mail_handlers as $handler) {
				$handeled |= $handler($mail,$mail_recipients,$login);
			}

			//None of the email addresses have been recognized and processed: Return failure notice
			if(!$handeled) {
				if(preg_match('/^(x-)?envelope-to:\s*([^\s@]*@'.preg_quote($domain).')\s*$/im',$rawheader,$m)) {
					if(!in_array($m[2],$mail_recipients['to']) && !in_array($m[2],$mail_recipients['cc'])) {
						ko_mailing_error($login, MAILING_ERROR_BCC_HINT, $mail, $m[2]);
						$handeled = true;
					}
				}
				if(!$handeled) {
					foreach($mail_recipients as $recipients) {
						foreach($recipients as $recipient) {
							if(substr($recipient,-strlen($domain)-1) == '@'.$domain) {
								$to = $recipient;
								break 2;
							}
						}
					}
					ko_mailing_error($login, MAILING_ERROR_INVALID_RECIPIENT, $mail, $to);
				}
			}

			//Delete message after it has been processed
			imap_delete($imap, $mail['msgno']);

		}//foreach(mails as mail)
	}

	//Close connection and expunge
	imap_close($imap, CL_EXPUNGE);


	ko_mailing_send_mails($mailer,$mails_per_cycle);


	//Check for old non-confirmed mails and delete them
	$limit = add2date(date('Y-m-d'), 'day', '-5', TRUE).' 00:00:00';
	$old_mails = db_select_data('ko_mailing_mails', "WHERE `status` = '".MAILING_STATUS_OPEN."' AND `crdate` < '".$limit."'",'*, NULL AS body');
	foreach($old_mails as $mail) {
		$log = '';
		$logcols = array('id', 'crdate', 'recipient', 'from', 'subject');
		foreach($logcols as $c) $log .= (getLL('mailing_header_'.$c) ? getLL('mailing_header_'.$c) : $c).': '.$mail[$c].', ';
		db_insert_data('ko_log', array('type' => 'mailing_delete_old', 'comment' => substr($log, 0, -2), 'user_id' => $mail['user_id'], 'date' => date('Y-m-d H:i:s')));
		db_delete_data('ko_mailing_mails', "WHERE `id` = '".$mail['id']."'");
	}

}//ko_mailing_main()


function ko_mailing_handle_mail_group(&$mail,$mail_recipients,$login) {
	global $access,$domain,$imap,$sender_email,$RECTYPES;

	if($login) {
		$no_access = FALSE;

		//Check access rights for found user
		//Access to the mailing module
		if(!ko_module_installed('mailing', $login['id'])) {
			$no_access = TRUE;
		}
		//Access to people module
		ko_get_access('leute', $login['id']);
		if(!ko_module_installed('leute', $login['id']) || $access['leute']['MAX'] < 1) {
			$no_access = TRUE;
		}
		// unset login_id, so that the following test will only consider the sender_email
		if ($no_access) {
			unset($login);
			unset($login['id']);
		}
		//Access rights for groups and smallgroups, check will be done further down
		ko_get_access('groups', $login['id']);
		ko_get_access('kg', $login['id']);
	}
	// continue also with sender email. Maybe it is allowed to send email to it's own group
	$sender_email = $mail['from'];

	$found_any = false;

	$crmProjects = ko_mailing_find_crm_projects($mail,$mail_recipients,$login,false);
	$crmProjectIds = array_column($crmProjects,'id');

	//flatten recipients array
	$mail_recipients = array_reduce($mail_recipients,'array_merge',array());

	foreach($mail_recipients as $mail_recipient) {
		$error = 0;
		$to = str_replace('@'.$domain, '', $mail_recipient);
		if($to == $mail_recipient) {
			// not the handeled domain -> ignore
			continue;
		}

		$unsetLogin = FALSE;

		// check if a rectype was specified in receiver address
		$recType = '';
		if (preg_match('/^.*\+[a-z]$/', $to)) {
			$x = explode('+', $to);
			$recType = array_pop($x);
			$to = implode('+', $x);

			if (!is_array($RECTYPES[$recType])) $recType = '';
		}

		//Check for automatically authorized emails
		$auto_confirmed = FALSE;
		if(FALSE !== strpos($to, '+')) {
			list($to, $auth) = explode('+', $to);
			if(KOOL_ENCRYPTION_KEY != '' && strlen($auth) == 32) {
				$auto_confirmed = md5(date('d').$to.KOOL_ENCRYPTION_KEY) == $auth;
			}
		}

		$new_code = false;
		$found = false;

		//Allow sending to groups without moderation. Will be set to TRUE in ko_mailing_check_group()
		$no_mod = FALSE;

		//Find mails sent to noreply (e.g. autoresponders)
		if($to == 'noreply') {
			$found = TRUE;
		}
		//Find confirm emails
		else if(substr($to, 0, strlen('confirm-')) == 'confirm-') {
			$found = TRUE;
			$code = substr($to, strlen('confirm-'));
			$error = ko_mailing_check_code($code, $mail2);

			if($error) {
				ko_mailing_error($login, $error, (is_array($mail2) ? $mail2 : $mail), $to);
			} else {
				ko_mailing_mail_confirmed($login, $code);
			}
		}
		//Find group with id
		else if(1 == preg_match('/^gr([0-9.]*$)/', $to, $m)) {
			$found = TRUE;
			list($all, $data) = $m;

			list($gid, $rid) = explode('.', $data);
			$error = ko_mailing_check_group($login, $gid, $rid, $no_mod, $unsetLogin);

			//Don't allow sender if setting prohibits addresses with no alias
			if(ko_get_setting('mailing_only_alias')) $error = MAILING_ERROR_ONLY_ALIAS;

			$mail['_recipient'] = 'gr'.$gid.($rid ? '.'.$rid : '');
			if($error) {
				ko_mailing_error($login, $error, $mail, $to);
			} else {
				$use_group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);

				// apply rectype of group if no recype was specified in receiver address
				if (!$recType) $recType = $use_group['mailing_rectype'];
				if (!is_array($RECTYPES[$recType])) $recType = '';
				$mail['_rectype'] = $recType;

				$mail['_reply_to'] = $use_group['mailing_reply_to'];
				$modifyRcpts = $use_group['mailing_modify_rcpts'];
				$mail['_to'] = ($recType ? $recType . '+' : '') . $to.'@'.$domain;
				// add prefix to email subject
				$prefix = trim($use_group['mailing_prefix']);
				if ($prefix != '' && strpos($mail['subject'], $prefix) === false)
					$mail['subject'] = $prefix . ' ' . trim($mail['subject']);
				if($use_group['mailing_crm_project_id']) {
					$crmProjectIds[] = $use_group['mailing_crm_project_id'];
				}
				list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $login, $modifyRcpts, $crmProjectIds);
			}
		}
		//Find smallgroup with id
		else if(1 == preg_match('/^sg([0-9]{4})([a-zA-Z.]*)$/', $to, $m)) {
			$found = TRUE;
			list($all, $sgid, $rid) = $m;

			$error = ko_mailing_check_smallgroup($login, $sgid, $rid, $unsetLogin);

			//Don't allow sender if setting prohibits addresses with no alias
			if(ko_get_setting('mailing_only_alias')) $error = MAILING_ERROR_ONLY_ALIAS;

			if($error) {
				ko_mailing_error($login, $error, $mail, $to);
			} else {
				$mail['_recipient'] = 'sg'.$sgid.($rid?'.'.$rid:'');
				$mail['_rectype'] = $recType;
				list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $login, TRUE, $crmProjectIds);
			}
		}
		//My List
		else if($to == 'ml') {
			$found = TRUE;
			$error = ko_mailing_check_mylist($login, $unsetLogin);

			if($error) {
				ko_mailing_error($login, $error, $mail, $to);
			} else {
				$mail['_recipient'] = 'ml';
				$mail['_rectype'] = $recType;
				list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $login, TRUE, $crmProjectIds);
			}
		}
		//Find filter preset with id
		else if(1 == preg_match('/^fp([0-9]*$)/', $to, $m)) {
			$found = TRUE;
			list($all, $data) = $m;

			$fid = intval($data);
			$error = ko_mailing_check_filter($login, $fid, $unsetLogin);

			//Don't allow sender if setting prohibits addresses with no alias
			if(ko_get_setting('mailing_only_alias')) $error = MAILING_ERROR_ONLY_ALIAS;

			if($error) {
				ko_mailing_error($login, $error, $mail, $to);
			} else {
				$mail['_rectype'] = $recType;
				$mail['_recipient'] = 'fp'.$fid;
				list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $login, TRUE, $crmProjectIds);
			}
		}
		//Find mailing alias
		else if(!ko_mailing_check_disallowed_alias_patterns(strtolower($to))) {

			//Find group or small group with this alias
			$groups = db_select_data('ko_groups', "WHERE LOWER(`mailing_alias`) = '".mysqli_real_escape_string(db_get_link(), strtolower($to))."'");
			$smallgroups = db_select_data('ko_kleingruppen', "WHERE LOWER(`mailing_alias`) = '".mysqli_real_escape_string(db_get_link(), strtolower($to))."'");
			$filters = db_select_data('ko_userprefs', "WHERE LOWER(`mailing_alias`) = '".mysqli_real_escape_string(db_get_link(), strtolower($to))."'");

			$num_found = sizeof($groups)+sizeof($smallgroups)+sizeof($filters);
			if($num_found == 0) {
				ko_mailing_error($login, MAILING_ERROR_NO_ALIAS_FOUND, $mail, $to);
			} else if($num_found > 1) {
				ko_mailing_error($login, MAILING_ERROR_NON_UNIQUE_ALIAS, $mail, $to);
			} else {
				if(sizeof($groups) == 1) {
					$group = array_shift($groups);
					$error = ko_mailing_check_group($login, $group['id'], '', $no_mod, $unsetLogin);
					if($error) {
						ko_mailing_error($login, $error, $mail, $to);
					} else {
						// apply rectype of group if no recype was specified in receiver address
						if (!$recType) $recType = $group['mailing_rectype'];
						if (!is_array($RECTYPES[$recType])) $recType = '';
						$mail['_rectype'] = $recType;

						$mail['_recipient'] = 'gr'.$group['id'];
						$mail['_reply_to'] = $group['mailing_reply_to'];
						$mail['_to'] = ($recType ? $recType . '+' : '') . $to.'@'.$domain;
						// add prefix to email subject
						$prefix = trim($group['mailing_prefix']);
						if ($prefix != '' && strpos($mail['subject'], $prefix) === false)
							$mail['subject'] = $prefix . ' ' . trim($mail['subject']);
						$modifyRcpts = $group['mailing_modify_rcpts'];
						if($group['mailing_crm_project_id']) {
							$crmProjectIds[] = $group['mailing_crm_project_id'];
						}
						list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $login, $modifyRcpts, $crmProjectIds);
						$found = TRUE;
					}
				} else if(sizeof($smallgroups) == 1) {
					$sg = array_shift($smallgroups);
					$error = ko_mailing_check_smallgroup($login, $sg['id'], NULL, $unsetLogin);
					if($error) {
						ko_mailing_error($login, $error, $mail, $to);
					} else {
						$mail['_rectype'] = $recType;
						$mail['_recipient'] = 'sg'.$sg['id'];
						list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $login, TRUE, $crmProjectIds);
						$found = TRUE;
					}
				} else if(sizeof($filters) == 1) {
					$fp = array_shift($filters);
					$error = ko_mailing_check_filter($login, $fp['id'], $unsetLogin);
					if($error) {
						ko_mailing_error($login, $error, $mail, $to);
					} else {
						$mail['_rectype'] = $recType;
						$mail['_recipient'] = 'fp'.$fp['id'];
						list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $login, TRUE, $crmProjectIds);
						$found = TRUE;
					}
				}
			}
		}

		if($new_id) {
			$mail['_id'] = $new_id;
		}

		if($new_code) {
			//Auto confirm email if auth check above passed
			if($auto_confirmed || $no_mod) {
				ko_mailing_mail_confirmed($unsetLogin?NULL:$login, $new_code);
			} else {
				$error = ko_mailing_send_moderation_mail($unsetLogin?NULL:$login, $new_id, $mail, $sender_email);
				if($error) ko_mailing_error($login, $error, $mail, $to);
			}
		}

		$found_any |= $found;

	}//foreach(mail_recipients)

	return $found_any;
}


/**
 * @param $mailer RawSmtpMailer
 * @param $mails_per_cycle
 *
 * @throws Exception
 */
function ko_mailing_send_mails($mailer,$mails_per_cycle) {
	global $MODULES,$return_path,$MAIL_TRANSPORT,$domain;

	//Check db for mails to be sent
	$sent_mails = 0;
	$mails = db_select_data('ko_mailing_mails', "WHERE `status` = '".MAILING_STATUS_CONFIRMED."'");
	foreach($mails as $mail) {
		if($mail['size']) {
			$mail['body'] = gzinflate($mail['body']);
		}

		$done_names = array();
		if ($sent_mails == $mails_per_cycle) break;

		//Find quoted-printable in header

		//Find utf-8 encoding. If set then encode recipient's name
		//if(FALSE !== strpos(strtolower($mail['header']), 'charset=utf-8')) $utf8 = TRUE;
		//else $utf8 = FALSE;

		//Get next recipients and send emails
		$where = "WHERE `mail_id` = '".$mail['id']."'";

		$restricted_leute_ids = ko_apply_leute_information_lock();
		if (!empty($restricted_leute_ids)) {
			$where.= " AND leute_id NOT IN('" . implode("','", $restricted_leute_ids) . "')";
		}

		$recipients = db_select_data('ko_mailing_recipients', $where, '*', '', 'LIMIT 0,'.($mails_per_cycle - $sent_mails > 0 ? ($mails_per_cycle - $sent_mails) : 0));

		$crmContactIds = array();
		if($recipients) {
			$crmProjectIds = explode(',',$mail['crm_project_ids']);
			$subject = iconv_mime_decode($mail['subject'],0,'latin1');
			foreach($crmProjectIds as $crmProjectId) {
				if(!$crmProjectId) continue;
				$contact = ko_mailing_store_crm_contact($mail['header'].CRLF.CRLF.$mail['body'],$crmProjectId,$mail['user_id'],null,$mail['id'],$subject);
				$crmContactIds[] = $contact['id'];
			}
		}

		//Set return path to sender's email
		$_return_path = ($return_path == 'USER') ? '-f'.$mail['from'] : $return_path;


		foreach($recipients as $rec) {
			if ($sent_mails == $mails_per_cycle) break;
			if(!$rec['id']) continue;
			if ($mail['modify_rcpts']) {
				$to = "To: ".mb_encode_mimeheader($rec['name'], 'UTF-8', 'Q')." <".$rec['email'].">" . CRLF;
			}
			else {
				$to = "";
			}
			$subject = "Subject: " . ko_mailing_markers($mail['subject'], $rec['leute_id'], $rec['email'], FALSE, $rec['placeholder_data']) . CRLF;
			$bulkHeader = ($bulk_header === true ? 'Precedence: bulk' . CRLF : '');
			$log_to = $rec['name']." (".$rec['email'].")";

			$sender = $mail['from'];
			$rcpt = $rec['email'];

			$mailContent = $mail['header'] . CRLF.CRLF . $mail['body'];
			$mailContent = ko_mailing_markers_by_part($mailContent, $rec);

			$parts = explode(CRLF.CRLF, $mailContent);
			array_shift($parts);
			$body = implode(CRLF.CRLF, $parts);

			$message = ko_emailtext(trim($mail['header'])).$to.$bulkHeader.$subject.CRLF.ko_emailtext($body);

			$mailer->removeAddresses();

			try {
				$mailer->setSender($sender);
				$mailer->addAddress($rcpt);
				$mailer->setMessage($message);
				$mailer->send();
				db_delete_data('ko_mailing_recipients', "WHERE `id` = '" . $rec['id'] . "'");
				$done[] = $rec['id'];
				$doneLeuteIds[] = $rec['leute_id'];
				$done_names[] = $log_to;
				$sent_mails++;

				foreach ($crmContactIds as $crmContactId) {
					db_insert_data('ko_crm_mapping', array('contact_id' => $crmContactId, 'leute_id' => $rec['leute_id']));
				}
			} catch (Exception $e) {
				ko_log('mailing_smtp_error', $e->getMessage());

				if(stristr($e->getMessage(), "SMTP Error: The following recipients failed:")) {
					// try to send mail a few times. after this: remove from queue and contact sender
					if ($rec['delivery_attempts'] >= ko_get_setting('mailing_max_attempts')) {
						$mailsubject = getLL("admin_mailing_max_attempts_subject");
						$mailtext = getLL("mailing_errormail_text_intro");
						$mailtext.= sprintf(getLL('admin_mailing_max_attempts_mailbody'), $domain)."\n";
						$mailtext.= "<strong>" . getLL('mailing_header_date') . "</strong>: " . sql2datetime($mail['crdate']) ."\n";
						$mailtext.= "<strong>" . getLL('mailing_header_subject') . "</strong>: " . $mail['subject'] ."\n";
						$mailtext.= "<strong>" . getLL('mailing_header_recipient') . "</strong>: " . $log_to ."\n";

						preg_match_all('/Reply-To: (.*)\r/', $mail['header'], $reply_to, PREG_SET_ORDER, 0);
						$reply_address = (!empty($reply_to[0][1]) ? $reply_to[0][1] : $mail['from']);
						ko_send_html_mail('', $reply_address, $mailsubject, ko_emailtext(nl2br($mailtext)));
						$where = "WHERE id = " . $rec['id'];
						db_delete_data("ko_mailing_recipients", $where);
					} else {
						$where = "WHERE id = " . $rec['id'];
						$data = ["delivery_attempts" => ($rec['delivery_attempts'] + 1)];
						db_update_data("ko_mailing_recipients", $where, $data);
					}
				}
			}

		}

		//Create log entry with all recipients
		db_insert_data('ko_log', array('type' => 'mailing_sent', 'comment' => $mail['id'].': '.implode(', ', $done_names), 'user_id' => $mail['user_id'], 'date' => date('Y-m-d H:i:s')));

		//Check recipients, mark mail as sent if none left
		$num = db_get_count('ko_mailing_recipients', 'id', "AND `mail_id` = '".$mail['id']."'");
		if($num == 0) {
			db_update_data('ko_mailing_mails', "WHERE `id` = '".$mail['id']."'", array('status' => MAILING_STATUS_SENT));
			//Add log entry after finishing mailing
			$log = $mail['id'].': '.'Subject: '.$mail['subject'].', From: '.$mail['from'].', To: '.$mail['recipient'];
			db_insert_data('ko_log', array('type' => 'mailing_done', 'comment' => $log, 'user_id' => $mail['user_id'], 'date' => date('Y-m-d H:i:s')));
		}
	}
}

function ko_mailing_find_crm_projects(&$mail,$mail_recipients,$login,$report_errors = true) {
	global $domain,$access;

	if($login) {
		ko_get_access('crm',$login['id']);
	}

	$projects = array();
	foreach($mail_recipients as $recipients) {
		foreach($recipients as $recipient) {
			$at = strrpos($recipient,'@');
			$to = substr($recipient,0,$at);
			$host = substr($recipient,$at+1);
			if($host == $domain && substr($to,0,3) == 'crm') {
				$project = ko_mailing_parse_crm_project(substr($to,3));
				if($project) {
					if(!$login) {
						if($report_errors) ko_mailing_error($login,MAILING_ERROR_CRM_NO_USER,$mail);
						return array();
					}
					if(!ko_module_installed('crm', $login['id'])) {
						if($report_errors) ko_mailing_error($login,MAILING_ERROR_CRM_NO_ACCESS,$mail);
						return array();
					}
					if(max($access['crm'][$project['id']],$access['crm']['ALL']) >= 2) {
						$projects[$recipient] = $project;
					} else if($report_errors) {
						ko_mailing_error($login,MAILING_ERROR_CRM_PROJECT_NO_ACCESS,$mail,$to);
					}
				} else if($report_errors) {
					ko_mailing_error($login,MAILING_ERROR_CRM_NO_PROJECT,$mail,$to);
					ko_log('mailing_crm_project_not_found', "could not find crm project by pattern ".substr($to,4).".");
				}
			}
		}
	}
	return $projects;
}

function ko_mailing_handle_mail_crm(&$mail,$mail_recipients,$login) {
	global $domain,$imap;

	$crmProjects = ko_mailing_find_crm_projects($mail,$mail_recipients,$login);
	$crmRecipientIds = array();

	foreach($mail_recipients as $recipients) {
		foreach($recipients as $recipient) {
			if(!isset($crmProjects[$recipient]) && substr($recipient,strrpos($recipient,'@')+1) != $domain) {
				$crmRec = ko_get_person_by_email($recipient);
				if ($crmRec) {
					$crmRecipientIds[] = $crmRec['id'];
				}
			}
		}
	}

	if(empty($crmProjects)) {
		return false;
	}

	foreach($crmProjects as $project) {
		$contact = ko_mailing_store_crm_contact(
			imap_fetchheader($imap,$mail['msgno']).CRLF.CRLF.imap_body($imap,$mail['msgno']),
			$project['id'],
			$login['id'],
			$mail['msgno'],
			isset($mail['_id']) ? $mail['_id'] : null
		);
		foreach($crmRecipientIds as $recId) {
			db_insert_data('ko_crm_mapping', array('contact_id' => $contact['id'], 'leute_id' => $recId));
		}
	}

	return true;
}

function ko_mailing_parse_crm_project($pattern) {
	if (!$pattern) return FALSE;

	if($pattern[0] == '-') {
		$pattern = substr($pattern,1);
	}

	$project = NULL;

	if (preg_match('/\d+/', $pattern)) {
		$project = db_select_data('ko_crm_projects', "WHERE `id` = {$pattern}", '*', '', '', TRUE);
		if ($project['id'] != $pattern) $project = NULL;
	}
	if (!$project && strpos($pattern, '-') !== FALSE) {
		$project = db_select_data('ko_crm_projects', "WHERE `number` = '{$pattern}'", '*', '', '', TRUE);
		if ($project['number'] != $pattern) $project = NULL;
	}
	if (!$project) {
		$title = str_replace(array(',', '.', '@', ' ', 'ä', 'ö', 'ü'), array('', '', '', '', 'ae', 'oe', 'ue'), strtolower($pattern));
		$project = db_select_data('ko_crm_projects', "WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(`title`), ' ', ''), ',', ''), '@', ''), '.', ''), 'ä', 'ae'), 'ü', 'ue'), 'ö', 'oe') = '{$title}'", '*', '', '', TRUE);
		if (str_replace(array(',', '.', '@', ' ', 'ä', 'ö', 'ü'), array('', '', '', '', 'ae', 'oe', 'ue'), strtolower($project['title'])) != $title) $project = NULL;
	}

	return $project;
}

function ko_mailing_get_sender_login(&$from) {
	global $LEUTE_EMAIL_FIELDS;

	//Find sender email address
	$pos1 = strrpos($from, '<');
	if(FALSE !== $pos1) {
		$pos2 = strpos($from, '>', $pos1);
		$from = substr($from, $pos1 + 1, ($pos2 ? $pos2 : strlen($from)) - $pos1 - 1);
	}
	if(!check_email($from)) return FALSE;
	$from = strtolower($from);


	//email fields
	$where_email = '';
	foreach($LEUTE_EMAIL_FIELDS as $field) {
		$where_email .= " LOWER(l.$field) = '".$from."' OR ";
	}

	$logins = db_select_data("ko_admin AS a LEFT JOIN ko_leute as l ON a.leute_id = l.id",
		"WHERE ($where_email LOWER(a.email) = '".mysqli_real_escape_string(db_get_link(), $from)."') AND (a.disabled = '0' OR a.disabled = '')",
		"a.id AS id");
	$login = array_shift($logins);
	return $login['id'];
}//ko_mailing_get_sender_login()


/**
 * Mark mail as confirmed and create recipient entries for all recipients in queue
 * @param string $login
 * @param string $code code of th email that was confirmed
 */
function ko_mailing_mail_confirmed($login, $code) {
	$mail = db_select_data('ko_mailing_mails', "WHERE `code` = '$code' AND `status` = '".MAILING_STATUS_OPEN."'", '*, NULL AS body', '', '', TRUE);

	//Get recipients
	$recipients = ko_mailing_get_recipients($login, $mail['recipient'], $dummy);

	//Create db entries
	$done_emails = array();
	foreach($recipients as $r) {
		$emails = NULL;

		// apply rectype if specified
		if ($mail['rectype']) {
			$p = ko_apply_rectype($r, $mail['rectype']);
			if ($p['email']) $emails = array($p['email']);
		}

		if (!is_array($emails)) ko_get_leute_email($r, $emails);
		foreach($emails as $email) {  //Include all email addresses, if several are set as preferred
			$email = trim($email);
			if(!check_email($email)) continue;
			//Don't send mail to same address twice
			if(!ko_get_setting('mailing_allow_double') && in_array($email, $done_emails)) continue;
			$done_emails[] = $email;

			$entry = array('mail_id' => $mail['id'], 'name' => $r['vorname'].' '.$r['nachname'], 'email' => $email, 'leute_id' => $r['id']);
			db_insert_data('ko_mailing_recipients', $entry);
		}
	}

	//Set status of mail to confirmed
	db_update_data('ko_mailing_mails', "WHERE `id` = '".$mail['id']."'", array('status' => MAILING_STATUS_CONFIRMED));

	//Create log entry
	$log = $mail['id'].': '.'Subject: '.$mail['subject'].', From: '.$mail['from'].', To: '.$mail['recipient'].', Recipients: '.sizeof($recipients).', Modify Recipients: ' . $mail['modify_recipients'] . ', RecType: ' . $mail['rectype'];
	db_insert_data('ko_log', array('type' => 'mailing_confirmed', 'comment' => $log, 'user_id' => $login['id'], 'date' => date('Y-m-d H:i:s')));
}//ko_mailing_mail_confirmed()




/**
 * Return a list of entries from ko_leute who are recipients of the given mailinglist
 * @param string $rec sgXXXX[.Y], grXXXXXX.YYYYYY, fpX or ml
 */
function ko_mailing_get_recipients($login, $rec, &$accessError = null) {
	global $access, $sender_email;

	$mode = substr($rec, 0, 2);
	$data = substr($rec, 2);
	$_recipients = array();
	$allow = false;
	$accessError = FALSE;

	$restricted_leute_ids = ko_apply_leute_information_lock();

	switch($mode) {
		case 'gr':
			$parts = explode('.', $data);
			$gid = array_shift($parts);
			$rid = array_shift($parts);
			$group = 'g'.$gid.($rid ? '[g:0-9]*r'.$rid : '');
			$where = "WHERE `groups` REGEXP '$group' AND `deleted` = '0' AND `hidden` = '0'";
			$where.= " AND id NOT IN ('" . implode("','", $restricted_leute_ids) . "')";
			$_recipients = db_select_data('ko_leute', $where);
			$g = db_select_data('ko_groups', 'where id = ' . $gid, '*', '', '', TRUE, TRUE);
			ko_mailing_check_sender_email_access($g, $sender_email, $allow, $allow_without_mod);
		break;

		case 'ml':
			$ids = unserialize(ko_get_userpref($login['id'], 'leute_my_list'));
			if(sizeof($ids) > 0) {
				$where = "WHERE `id` IN ('".implode("','", $ids)."') AND `deleted` = '0' AND `hidden` = '0'";
				$where.= " AND id NOT IN ('" . implode("','", $restricted_leute_ids) . "')";
				$_recipients = db_select_data('ko_leute', $where);
			}
		break;

		case 'sg':
			$parts = explode('.', $data);
			$sgid = array_shift($parts);
			$rid = array_shift($parts);
			$where = "WHERE `smallgroups` REGEXP '".$sgid.($rid?':'.$rid:'')."' AND `deleted` = '0' AND `hidden` = '0'";
			$where.= " AND id NOT IN ('" . implode("','", $restricted_leute_ids) . "')";
			$_recipients = db_select_data('ko_leute', $where);
		break;

		case 'fp':
			$fid = intval($data);
			$filterPreset = db_select_data('ko_userprefs', "WHERE `type` = 'filterset' AND `id` = '$fid'", '*', '', '', TRUE);
			$filter = unserialize($filterPreset['value']);

			if(!$filter) $where = 'AND 1=2';
			else apply_leute_filter($filter, $where);

			$where = "WHERE 1=1 ".$where." AND `deleted` = '0' AND `hidden` = '0'";
			$where.= " AND id NOT IN ('" . implode("','", $restricted_leute_ids) . "')";
			$_recipients = db_select_data('ko_leute', $where);
		break;
	}

	//Perform tests
	ko_get_access('leute', $login['id']);
	$recipients = array();
	foreach($_recipients as $r) {
		//Check for access to this single address
		if($login['id'] > 0 && $access['leute']['ALL'] < 1 && $access['leute'][$r['id']] < 1) {
			$accessError = TRUE;
			continue;
		}
		//Check for valid email
		if(FALSE === ko_get_leute_email($r, $emails)) continue;

		$recipients[] = $r;
	}

	return $recipients;
}//ko_mailing_get_recipients()




/**
 * Retrieve a human readable name of a given recipient (gr000001, sg000001, ml)
 */
function ko_mailing_get_rec_name($login, $rec) {
	global $all_groups;

	$mode = substr($rec, 0, 2);
	$data = substr($rec, 2);

	switch($mode) {
		case 'gr':
			if(!$all_groups) $all_groups = db_select_data('ko_groups', 'WHERE 1');

			$parts = explode('.', $data);
			$gid = array_shift($parts);
			//Get full id for the group
			$motherline = ko_groups_get_motherline($gid, $all_groups);
			$mids = array();
			foreach($motherline as $mg) {
				$mids[] = 'g'.$all_groups[$mg]['id'];
			}
			$full_id = (sizeof($mids) > 0 ? implode(':', $mids).':' : '').'g'.$gid;
			//Add role
			$rid = array_shift($parts);
			if($rid) $full_id .= ':r'.$rid;

			return getLL('mailing_group').' '.ko_groups_decode($full_id, 'group_desc_full');
		break;

		case 'sg':
			$parts = explode('.', $data);
			$sgid = array_shift($parts);
			$sg = db_select_data('ko_kleingruppen', "WHERE `id` = '".$sgid."'", '*', '', '', TRUE);
			return getLL('mailing_smallgroup').' '.$sg['name'];
		break;

		case 'ml':
			return $login['login']."'s ".getLL('mailing_mylist');
		break;
	}
}//ko_mailing_get_rec_name()




/**
 * Send error email to sender if valid and create log entry about the error
 *
 * @param $error: Error number that occured
 * @param $mail: Mail array that caused the error
 * @param $to: If given, contains the current recipient that caused the problem
 */
function ko_mailing_error($login, $error, $mail, $to='') {
	global $domain, $done_error_mails, $max_recipients, $return_path;

	//Only send one error message for each mail
	if(in_array($mail['id'], $done_error_mails)) return;
	$done_error_mails[] = $mail['id'];

	if(check_email($mail['from'])) {
		//Prepare mapping array
		$map = array();
		$map['@MAX_RECIPIENTS@'] = $max_recipients;
		if($to) $map['@CURRENT_TO@'] = $to;
		else $map['@CURRENT_TO@'] = '';
		foreach($mail as $k => $v) {
			$map['@'.strtoupper($k).'@'] = $v;
		}

		//Send error email
		$message  = getLL('mailing_errormail_text_intro');
		$message .= strtr(getLL('mailing_errormail_text_'.$error), $map);
		$message .= "\n\n".ko_mailing_summary($login, $mail);

		ko_send_mail('noreply@'.$domain, $mail['from'], getLL('mailing_errormail_subject'), $message);
	}//if(check_email(from))


	//Create log entry
	$log = 'ERROR '.$error.' '.getLL('mailing_error_'.$error).' - ';
	foreach($mail as $k => $v) {
		if($k && $v) $log .= $k.': '.$v.', ';
	}
	if(substr($log, 0, -2) == ', ') $log = substr($log, 0, -2);
	db_insert_data('ko_log', array('type' => 'mailing_error', 'comment' => $log, 'user_id' => $login['id'], 'date' => date('Y-m-d H:i:s')));
}//ko_mailing_error()




function ko_mailing_store_moderation($imap, $mail, $login, $modifyRcpts = true, $crmProjectIds = array()) {
	global $sender_email,$unreferencedCrmContacts;

	//Get email body and create email code
	$body = imap_body($imap, $mail['msgno']);
	$code = md5(uniqid(mt_rand(), true));

	//Get header and delete some entries
	$header = imap_fetchheader($imap, $mail['msgno']);
	if ($modifyRcpts) {
		$header = preg_replace('/(\n|^)To:(.*)(\n\s+(.*))*\n/i', '$1', $header);
		$header = preg_replace('/(\n|^)Cc:(.*)(\n\s+(.*))*\n/i', '$1', $header);
	}
	$header = preg_replace('/(\n|^)Bcc:(.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)Subject:(.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)Delivered-To:(.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)Envelope-To:(.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)X-Spam-Score:(.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)X-Spam-Flag:(.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)X-Spam-Report:(.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)DKIM-Signature:(.*)(\n\s+(.*))*\n/i', '$1', $header);

	$manualReplyTo = FALSE;
	$replyTo = '';
	if($mail['_reply_to']) {
		//Remove Reply-To
		$header = preg_replace('/(\n|^)Reply-To:(.*)(\n\s+(.*))*\n/i', '$1', $header);
		//Set new Reply-To
		switch($mail['_reply_to']) {
			case 'list':
				$replyTo = $mail['_to'];
			break;
			case 'sender':
			default:
				$replyTo = $mail['from'];
			break;
		}
		$header = trim($header)."\nReply-To: ".$replyTo."\n";
		$manualReplyTo = TRUE;
	}


	//Set from address to an spf save sender address
	$header = preg_replace('/(\n|^)From:(.*)(\n\s+(.*))*\n/i', '$1', $header);

	if(!$manualReplyTo) {
		$header = trim($header)."\nReply-To: ".$mail['from']."\n";
	}

	$mailFrom = $mail['from'];
	ko_mail_get_spf_from($mailFrom, $replyTo, $mailSender, $login['id']);

	$fromEmail = array_key_first($mailFrom);
	$from = '"'.str_replace('"', '', $mailFrom[$fromEmail]).'" <'.$fromEmail.'>';
	$header = trim($header)."\nFrom: ".$from."\n";

	if($mailSender) {
		$senderEmail = array_key_first($mailSender);

		//Replace mail[from] with sender which will be used for sending the email, so it ends up in Return-Path
		$mail['from'] = $senderEmail;

		$sender = '"'.str_replace('"', '', $mailSender[$senderEmail]).'" <'.$senderEmail.'>';
		$header = trim($header)."\nSender: ".$sender."\n";
	}


	//Create db entry for email
	$entry = array();
	$entry['body'] = gzdeflate($body,9);
	$entry['header'] = $header;
	$entry['size'] = strlen($header)+strlen($body)+strlen(CRLF.CRLF);
	$entry['status'] = MAILING_STATUS_OPEN;
	$entry['crdate'] = date('Y-m-d H:i:s');
	$entry['code'] = $code;
	$entry['subject'] = $mail['subject'];
	$entry['from'] = $mail['from'];
	$entry['user_id'] = $login['id'];
	$entry['sender_email'] = $sender_email;
	$entry['recipient'] = $mail['_recipient'];
	$entry['modify_rcpts'] = $modifyRcpts;
	$entry['rectype'] = $mail['_rectype'];
	$entry['crm_project_ids'] = implode(',',array_unique($crmProjectIds));
	$new_id = db_insert_data('ko_mailing_mails', $entry);

	if(isset($unreferencedCrmContacts[$mail['msgno']])) {
		db_update_data('ko_crm_contacts','WHERE `id` = '.$unreferencedCrmContacts[$mail['msgno']],array('reference' => 'ko_mailing_mails:'.$new_id));
		unset($unreferencedCrmContacts[$mail['msgno']]);
	}

	//Create log entry
	$log = $new_id.': Subject: '.$mail['subject'].', From: '.$mail['from'].', To: '.$mail['to'].', Size: '.$mail['size']. ', ModifyRecipients: ' . $modifyRcpts . ', RecType: ' . $mail['rectype'];
	db_insert_data('ko_log', array('type' => 'mailing_new', 'comment' => $log, 'user_id' => $login['id'], 'date' => date('Y-m-d H:i:s')));

	return array($new_id, $code);
}//ko_mailing_store_moderation()


function ko_mailing_store_crm_contact($rawMail,$projectId,$loginId,$msgno,$mailId,$subject = null) {
	global $unreferencedCrmContacts,$BASE_PATH;
	static $contacts;

	$cacheKeys = array();
	if($msgno) {
		$cacheKeys[] = 'msgno:'.$msgno.':'.$projectId;
	}
	if($mailId) {
		$cacheKeys[] = 'mailId:'.$mailId.':'.$projectId;
	}

	foreach($cacheKeys as $cacheKey) {
		if(isset($contacts[$cacheKey])) {
			$contact = $contacts[$cacheKey];
		}
	}

	if(!$contact && $mailId) {
		ko_get_crm_contacts($contact, " AND `reference` = 'ko_mailing_mails:".$mailId."' AND `project_id` = '".$projectId."'", '', '', TRUE, TRUE);
	}

	if(!$contact) {
		require_once($BASE_PATH.'inc/MimeMailParser/Message.php');
		$message = new kOOL\MimeMailParser\Message('latin1');
		$message->parse($rawMail);

		$mailtext = implode("\n\n".str_repeat('-',40)."\n\n",array_filter(array_map('trim',$message->getAllTextBodies(true))));

		if(!$subject) {
			$subject = $message->getHeader('subject');
		}

		$contact = array(
			'type' => 'email',
			'date' => $message->getDate()->format('Y-m-d H:i:s'),
			'title' => $subject,
			'description' => nl2br($mailtext),
			'project_id' => $projectId,
			'crdate' => date('Y-m-d H:i:s'),
			'cruser' => $loginId,
		);
		if($mailId) {
			$contact['reference'] = 'ko_mailing_mails:'.$mailId;
		}

		$contact['id'] = db_insert_data('ko_crm_contacts', $contact);
		if(!$mailId && $msgno) {
			$unreferencedCrmContacts[$msgno] = $contact['id'];
		}

		// files
		$files = $message->getAllAttachments();
		$files = array_filter($files,function($file) {
			return $file->getContentType() != 'text/plain';
		});
		if (sizeof($files) > 1) {
			$zip = new ZipArchive();
			$fileName = "my_images/kota_ko_crm_contacts_file_".$contact['id'].".zip";
			$zip->open("{$BASE_PATH}{$fileName}", ZIPARCHIVE::CREATE);
			foreach ($files as $f) {
				$zip->addFromString($message->getFilename($f),$message->getBody($f));
			}
			$zip->close();
		} else if (sizeof($files) == 1) {
			$file = reset($files);
			$parts = explode('.', $message->getFilename($file));
			$ending = array_pop($parts);

			$fileName = "my_images/kota_ko_crm_contacts_file_".$contact['id'].".".$ending;
			file_put_contents($BASE_PATH.$fileName,$message->getBody($file));
		} else {
			$fileName = '';
		}
		db_update_data('ko_crm_contacts', "WHERE `id` = ".$contact['id'], array('file' => $fileName));
	}

	if(empty($contact['reference']) && $mailId) {
		$reference = 'ko_mailing_mails:'.$mailId;
		db_update_data('ko_crm_contacts','WHERE `id`='.$contact['id'],array('reference' => $reference));
		$contact['reference'] = $reference;
	}

	foreach($cackeKeys as $cacheKey) {
		$contacts[$cacheKey] = $contact;
	}

	return $contact;
}


/**
 * Sends a moderation email
 * Either to the sending db_user or to the moderators of a recipient group (if sender_email is set)
 *
 * @param int $mid Mailing ID
 * @param array $mail IMAP mail
 * @param array $db_user DB user. Row from ko_admin
 * @param string $sender_email Email address if not login was found
 * @access public
 * @return int Errorcode or 0 if no error
 */
function ko_mailing_send_moderation_mail($login, $mid, $mail, $sender_email) {
	global $domain, $return_path;

	if(!$mid) return MAILING_ERROR_MODERATION_EMAIL;

	$mailing = db_select_data('ko_mailing_mails', "WHERE `id` = '$mid'", '*', '', '', TRUE);
	if($mailing['size']) {
		$mailing['body'] = gzinflate($mailing['body']);
	}
	if(!$mailing['id'] || $mailing['id'] != $mid || $mailing['status'] != MAILING_STATUS_OPEN) return MAILING_ERROR_MODERATION_EMAIL;

	//Get email of logged in user (for moderation email)
	if($login['id']) {
		$person = ko_get_logged_in_person($login['id']);
		if(check_email($person['email'])) $to = array($person['email']);
		else if(check_email($login['email'])) $to = array($login['email']);
	}
	//If sender_email given then get moderators for the recipient group
	else if($sender_email) {
		$gid = format_userinput(str_replace('gr', '', $mail['_recipient']), 'uint');
		$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
		if(!$group['id'] || $group['id'] != $gid) return MAILING_ERROR_INVALID_GROUP_ID;

		//Get moderators for this group
		$to = ko_mailing_get_moderators_by_group($group);
	}
	else {
		return MAILING_ERROR_MODERATION_EMAIL;
	}

	//If no moderator then send email back to sender with error message
	if(sizeof($to) == 0) {
		return MAILING_ERROR_MODERATION_EMAIL;
	}

	//Send moderation emails
	$confirm_email = 'confirm-'.$mailing['code'].'@'.$domain;
	$replyTo = array($confirm_email => '');

	$subject = getLL('mailing_confirm_subject').': '.$mail['subject'];
	$message = sprintf(getLL('mailing_confirm_text'), $confirm_email)."\n\n".ko_mailing_summary($login, $mail, $mailing['body']);
	foreach($to as $t) {
		if(!check_email($t)) continue;
		ko_send_mail(ko_get_setting('info_email'), $t, $subject, $message, array(), array(), array(), $replyTo);
	}

	return 0;
}//ko_mailing_send_moderation_mail()




function ko_mailing_summary($login, $mail, $body='') {
	global $imap;

	$summary = '';


	//Get name of recipient (group or other) for a nicer display
	$rec_name = ko_mailing_get_rec_name($login, $mail['_recipient']);
	if($rec_name) $summary .= getLL('mailing_rec_name').': '.$rec_name."\n";

	//Add number of recipients
	$recipients = ko_mailing_get_recipients($login, $mail['_recipient']);
	if(sizeof($recipients) > MAILING_MAX_RECIPIENTS_FOR_SUMMARY) {
		$summary .= getLL('mailing_number_of_recipients').': '.sizeof($recipients)."\n";
	} else {
		$recEmails = array();
		foreach($recipients as $rec) {
			$recEmails[] = '- '.$rec['email'].' ('.trim($rec['vorname'].' '.$rec['nachname'].' '.$rec['firm']).')';
		}
		$summary .= getLL('mailing_recipients').":\n".implode("\n", $recEmails)."\n";
	}
	$summary .= "\n";

	//Show parts of the header
	$show_headers = array('from', 'to', 'cc', 'subject', 'date');
	foreach($mail as $k => $v) {
		if(!in_array($k, $show_headers)) continue;
		if($v) $summary .= getLL('mailing_header_'.$k).': '.$v."\n";
	}

	//Add part of the body
	if($body) {
		//Get clear text email body
		$bodytext = trim(imap_fetchbody($imap, $mail['msgno'], '1.1'));
		if(!$bodytext) $bodytext = trim(imap_fetchbody($imap, $mail['msgno'], '1'));
		if(!$bodytext) $bodytext = trim(imap_fetchbody($imap, $mail['msgno'], '2.1'));

		$summary .= "\n".($bodytext ? $bodytext : (substr($body, 0, 200)."\n[...]\n"));
	}

	return $summary;
}//ko_mailing_summary()





/**
 * Check a given group and role id
 *
 * - Checks for valid group id
 * - Checks for access to this group
 * - Check for valid role assigned to the given group
 * - Check for number of recipients to be greater than 0 and smaller than $max_recipients
 *
 * returns 0 if OK or error code if not OK
 */
function ko_mailing_check_group($login, $gid, $rid, &$no_mod, &$unsetLogin) {
	global $max_recipients, $access, $sender_email;

	//Default to moderation
	$no_mod = FALSE;
	$no_mod_l = false;
	$no_mod_s = false;

	//Check for correct gid
	if(!$gid || strlen($gid) != 6 || !ctype_digit($gid)) return MAILING_ERROR_INVALID_GROUP_ID;

	//Check role id if given
	if($rid) {
		if(strlen($rid) != 6 || !ctype_digit($rid)) return MAILING_ERROR_INVALID_GROUP_ROLE_ID;
	}

	//Check for valid group in DB
	$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
	if(!$group['id'] || $group['id'] != $gid) return MAILING_ERROR_INVALID_GROUP_ID;
	if($rid && !in_array($rid, explode(',', $group['roles']))) return MAILING_ERROR_INVALID_GROUP_ID;

	$no_group_access_error_l = FALSE;
	$no_group_access_error_s = FALSE;

	if($login['id'] > 0) {
		if(!ko_module_installed('groups', $login['id']) || ($access['groups']['ALL'] < 1 && $access['groups'][$gid] < 1)) {
			$no_group_access_error_l = TRUE;
			$unsetLogin1 = TRUE;
		}

		if($group['mailing_mod_logins'] > 0) $no_mod_l = TRUE;
	}
	else {
		$no_group_access_error_l = TRUE;
	}

	//Check for access by sender_email
	ko_mailing_check_sender_email_access($group, $sender_email, $allow, $no_mod_s);
	$no_group_access_error_s = !$allow;

	if ($no_group_access_error_l && $no_group_access_error_s) {
		return MAILING_ERROR_GROUP_NO_ACCESS_EMAIL;
	}
	else if ($no_group_access_error_s) {
		$no_mod = $no_mod_l;
	}
	else if ($no_group_access_error_l) {
		$no_mod = $no_mod_s;
	}
	else {
		$no_mod = $no_mod_s || $no_mod_l;
	}

	//Check for recipients
	$recipients = ko_mailing_get_recipients($login, 'gr'.$gid.($rid ? '.'.$rid : ''), $unsetLogin2);
	if(sizeof($recipients) <= 0) return MAILING_ERROR_NO_RECIPIENTS;
	if($max_recipients > 0 && sizeof($recipients) > $max_recipients) return MAILING_ERROR_TOO_MANY_RECIPIENTS;

	$unsetLogin = $unsetLogin1 || $unsetLogin2;

	return 0;
}//ko_mailing_check_group()


/**
 * @param $group                        -> the recipient group
 * @param string $sender_email the sender's    -> email address
 * @param $no_group_access_error        -> PASS_BY_REFERENCE
 * @param $no_mod                       -> PASS_BY_REFERENCE
 */
function ko_mailing_check_sender_email_access($group, $sender_email, &$allow, &$allow_without_mod) {

	$allow = $allow_without_mod = false;
	//Check for access by sender_email
	if ($sender_email != '') {
		//Check for allowed sender (group member or moderation)
		$allow_send = $allow_send_mod = FALSE;
		$sender_roles = ko_mailing_get_sender_roles($group, $sender_email);
		//Check for moderator access
		if(in_array('moderator', $sender_roles)) {
			if($group['mailing_mod_logins'] > 0) $allow_send = TRUE;
			else $allow_send_mod = TRUE;
		}
		//Check for member access
		if(in_array('member', $sender_roles)) {
			if($group['mailing_mod_members'] == 2) $allow_send = TRUE;
			else if($group['mailing_mod_members'] == 1)  $allow_send_mod = TRUE;
		}
		//Check for other access
		if($group['mailing_mod_others'] == 2) $allow_send = TRUE;
		else if($group['mailing_mod_others'] == 1)  $allow_send_mod = TRUE;

		if($allow_send || $allow_send_mod) {
			$allow = TRUE;
		}
		$allow_without_mod = $allow_send;
	}
} // ko_mailing_check_sender_email_access()





/**
 * Find the role of an email address within a group.
 * Try to find people among the addresses with the given email address and find their role inside the given group.
 * Role may be 'member' for regular group members of 'moderator'.
 */
function ko_mailing_get_sender_roles(&$group, $sender_email) {
	global $LEUTE_EMAIL_FIELDS;

	$roles = array();

	$where = array();

	foreach($LEUTE_EMAIL_FIELDS as $field) {
		$where[] = " `$field` = '".mysqli_real_escape_string(db_get_link(), $sender_email)."' ";
	}

	$people = db_select_data('ko_leute', "WHERE 1 AND (".implode(' OR ', $where).") AND `deleted` = '0' AND `hidden` = '0'");
	foreach($people as $p) {
		//Find members. Just a member of the group not regarding the role
		if(FALSE !== strpos($p['groups'], 'g'.$group['id'])) {
			$roles[] = 'member';
		}
		//Find moderator for the selected moderator role for this group
		if($group['mailing_mod_role'] && $group['mailing_mod_role'] != '_none' && in_array($group['mailing_mod_role'], explode(',', $group['roles'])) && FALSE !== strpos($p['groups'], 'g'.$group['id'].':r'.$group['mailing_mod_role'])) {
			$roles[] = 'moderator';
		}
		//Find moderator if mod_role is set to _none
		if($group['mailing_mod_role'] == '_none' && 1 == preg_match('/g'.$group['id'].'(,|$)/', $p['groups'])) {
			$roles[] = 'moderator';
		}
	}

	$roles = array_unique($roles);

	return $roles;
}//ko_mailing_get_sender_roles()





/**
 * Find email addresses of moderators for a given group according to this group's setting mailing_mod_role.
 *
 * @param mixed $group Group to find moderators for (array from ko_groups)
 * @return array A unique list of email addresses of all moderators. Empty array if none.
 */
function ko_mailing_get_moderators_by_group(&$group) {
	if($group['mailing_mod_role'] == '_none') {
		$where = "`groups` REGEXP 'g".$group['id']."(,|$)'";
	}
	else if($group['mailing_mod_role'] != '') {
		$where = "`groups` LIKE '%g".$group['id'].":r".$group['mailing_mod_role']."%'";
	}
	else {
		return array();
	}

	$r = array();
	$mods = db_select_data('ko_leute', "WHERE `deleted` = '0' AND `hidden` = '0' AND ".$where);
	foreach($mods as $mod) {
		if(TRUE === ko_get_leute_email($mod, $emails)) {
			foreach($emails as $email) {
				$r[] = $email;
			}
		}
	}
	return array_unique($r);
}//ko_mailing_get_moderators_by_group()





/**
 * Check for MyList as recipient
 *
 * - Check for access to people module
 * - Check for number of recipients to be greater than 0 and smaller than $max_recipients
 *
 * returns 0 if OK or error code if not OK
 */
function ko_mailing_check_mylist($login, &$unsetLogin) {
	global $max_recipients, $access;

	//Check for access
	if($access['leute']['MAX'] < 1) return MAILING_ERROR_MYLIST_NO_ACCESS;

	//Check for recipients
	$recipients = ko_mailing_get_recipients($login, 'ml', $unsetLogin);
	if(sizeof($recipients) <= 0) return MAILING_ERROR_MYLIST_EMPTY;
	if($max_recipients > 0 && sizeof($recipients) > $max_recipients) return MAILING_ERROR_TOO_MANY_RECIPIENTS;

	return 0;
}//ko_mailing_check_mylist()





/**
 * Check for filter preset as recipient
 *
 * - Check for access to people module
 * - Check for number of recipients to be greater than 0 and smaller than $max_recipients
 *
 * returns 0 if OK or error code if not OK
 */
function ko_mailing_check_filter($login, $fid, &$unsetLogin) {
	global $max_recipients, $access;

	//Check for access
	if($access['leute']['MAX'] < 1) return MAILING_ERROR_LEUTE_NO_ACCESS;

	//Check for recipients
	$recipients = ko_mailing_get_recipients($login, 'fp'.$fid, $unsetLogin);
	if(sizeof($recipients) <= 0) return MAILING_ERROR_FILTER_EMPTY;
	if($max_recipients > 0 && sizeof($recipients) > $max_recipients) return MAILING_ERROR_TOO_MANY_RECIPIENTS;

	return 0;
}//ko_mailing_check_filter()




/**
 * Check for MyList as recipient
 *
 * - Check for correct small group id and role
 * - Check for access to people module
 * - Check for number of recipients to be greater than 0 and smaller than $max_recipients
 *
 * returns 0 if OK or error code if not OK
 */
function ko_mailing_check_smallgroup($login, $sgid, $rid, &$unsetLogin) {
	global $SMALLGROUPS_ROLES, $max_recipients, $access;

	//Check for correct sgid
	if(!$sgid || strlen($sgid) != 4) return MAILING_ERROR_INVALID_SMALLGROUP_ID;
	for($i=0; $i<strlen($sgid); $i++) {
		if(!in_array(substr($sgid, $i, 1), array(0,1,2,3,4,5,6,7,8,9))) return MAILING_ERROR_INVALID_SMALLGROUP_ID;
	}

	//Check for access
	if($access['kg']['ALL'] < 1 || ($access['kg']['ALL'] < 2 && !in_array($sgid, kg_get_users_kgid($login['id'])))) return MAILING_ERROR_SMALLGROUP_NO_ACCESS;

	//Check role id if given
	if($rid) {
		if(strlen($rid) != 1) return MAILING_ERROR_INVALID_SMALLGROUP_ID;
		if(!in_array($rid, $SMALLGROUPS_ROLES)) return MAILING_ERROR_INVALID_SMALLGROUP_ID;
	}

	//Check for valid smallgroup in DB
	$sg = db_select_data('ko_kleingruppen', "WHERE `id` = '$sgid'", 'id', '', '', TRUE);
	if(!$sg['id'] || $sg['id'] != $sgid) return MAILING_ERROR_INVALID_SMALLGROUP_ID;

	//Check for recipients
	$recipients = ko_mailing_get_recipients($login, 'sg'.$sgid.($rid?'.'.$rid:''), $unsetLogin);
	if(sizeof($recipients) <= 0) return MAILING_ERROR_INVALID_SMALLGROUP_ID;
	if($max_recipients > 0 && sizeof($recipients) > $max_recipients) return MAILING_ERROR_TOO_MANY_RECIPIENTS;

	return 0;
}//ko_mailing_check_smallgroup()





function ko_mailing_check_code($code, &$mail2) {
	//Check for valid md5 hash
	if(strlen($code) != 32) return MAILING_ERROR_INVALID_CODE;
	for($i=0; $i<strlen($code); $i++) {
		if(!in_array(substr($code, $i, 1), array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f'))) return MAILING_ERROR_INVALID_CODE;
	}

	//Check db for mail with this code
	$mail = db_select_data('ko_mailing_mails', "WHERE `code` = '$code' AND `status` = '".MAILING_STATUS_OPEN."'", '`id`,`code`', '', '', TRUE);
	if(!$mail['id'] || $mail['code'] != $code) {
		$mail2 = db_select_data('ko_mailing_mails', "WHERE `code` = '$code'", '`status`', '', '', TRUE);
		if($mail2['status'] == MAILING_STATUS_CONFIRMED || $mail2['status'] == MAILING_STATUS_SENT) {
			return MAILING_ERROR_CODE_ALREADY_CONFIRMED;
		} else {
			return MAILING_ERROR_INVALID_CODE;
		}
	}

	return 0;
}//ko_mailing_check_code()




function ko_mailing_markers_by_part($part, $rec) {
	$parts = explode(CRLF.CRLF, $part);
	if (sizeof($parts) > 1 && preg_match('/content-type: multipart\/[^;]*;[ \t]*(?:\r\n[ \t]+[^\r\n]+)*(?:\r\n)?[ \t]+boundary=(?:"([^"]+)"|([^\s]+)\s)/i', $parts[0], $matches)) {
		$delimiter = '--'.$matches[1];
		$ps = explode($delimiter, $part);
		for ($i = 1; $i < sizeof($ps); $i++) {
			$p = ko_mailing_markers_by_part($ps[$i], $rec);
			$ps[$i] = $p;
		}
		return implode($delimiter, $ps);
	} else if (preg_match('/content-type: text/i', $part)) { // only replace something if content type starts with 'text' (text/html, text/plain, ...)
		if(FALSE !== strpos(strtolower($part), 'content-transfer-encoding: quoted-printable')) $qp = TRUE;
		else $qp = FALSE;

		$ps = explode(CRLF.CRLF, $part);
		$header = array_shift($ps);
		$body = implode(CRLF.CRLF, $ps);

		return $header . CRLF.CRLF . ko_mailing_markers($body, $rec['leute_id'], $rec['email'], $qp, $rec['placeholder_data']);
	} else {
		return $part;
	}
}




/**
 * Replaces markers in email part for each recipient
 * @param string $string The string where the replacing should happen (Usually subject or email text)
 * @param int $leute_id The ID of DB.ko_leute for the current recipient
 * @param string $email The email address of the current recipient. Must be given separately as it has been specified by ko_get_leute_email()
 *                      The hash code needs to be created with the recipients email address
 * @param boolean $qp Set to true to add new content in email part as quoted-printable text (needed if email itself is quoted-printable encoded
 * @return string New string with all markers replaced
 */
function ko_mailing_markers($string, $leute_id, $email, $qp=FALSE, $placeholderJSON=null) {
	global $edit_base_link;

	ko_get_person_by_id($leute_id, $p);
	if(!$p['id'] || $p['id'] != $leute_id) return $string;

	$key = $leute_id . '_' . $email['id'] . '_' . ($qp?'1':'0') . '_' . sha1($placeholderJSON?$placeholderJSON:'##NONE##') . '_' . sha1($string);
	if (isset($GLOBALS['kOOL']['mailingMarkerMap'][$key])) $map = $GLOBALS['kOOL']['mailingMarkerMap'][$key];
	else {
		$map = array();

		if ($placeholderJSON && ($placeholderData = json_decode($placeholderJSON)) && sizeof($placeholderData) > 0) { // used for mails to families
			array_walk_recursive($placeholderData, 'utf8_decode_array');
			foreach($placeholderData as $key => $value) {
				$map['###'.strtoupper($key).'###'] = $value;
			}
		} else { // email to person
			if($qp) $decodedText = imap_qprint($string);
			else $decodedText = $string;

			if (preg_match_all('/###([^#\n]+)###/', $decodedText, $matches)) {
				$tags = $matches[1];

				if (!isset($GLOBALS['kOOL']['allLeuteColNames'])) {
					$GLOBALS['kOOL']['allLeuteColNames'] = ko_get_leute_col_name(TRUE, TRUE, 'view', TRUE);
				}
				$colNames = $GLOBALS['kOOL']['allLeuteColNames'];

				if (!isset($GLOBALS['kOOL']['allDatafields'])) {
					$GLOBALS['kOOL']['allDatafields'] = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");
				}
				$allDatafields = $GLOBALS['kOOL']['allDatafields'];

				foreach ($colNames as $col => $colName) {
					if (in_array(strtoupper($col), $tags)) {
						$v = map_leute_daten($p[$col], $col, $p, $allDatafields);
						$map['###' . strtoupper($col) . '###'] = $v ? $v : '';
					}
				}

				//kOOL ID to be used in e.g. kool_groupsusbscribe
				$map['###_KID###'] = $leute_id . 'p' . substr(md5($leute_id . KOOL_ENCRYPTION_KEY), 0, 10);
				$map['###ABSENCE###'] = kota_mailing_ko_leute_absence($p);

				$gender = $p['geschlecht'];
				if(!$gender) {
					if($p['anrede'] == 'Herr') $gender = 'm';
					else if($p['anrede'] == 'Frau') $gender = 'w';
				}
				$map['###_SALUTATION###'] = getLL('mailing_salutation_'.$gender);
				$map['###_SALUTATION_FORMAL###'] = getLL('mailing_salutation_formal_'.$gender);
				$map['###TELEGRAMLINK###'] = ko_create_telegram_link($p['id']);
			}
		}

		//Add edit links to newsletter form on external webpage (e.g. TYPO3 page with extension kool_directmail)
		if($edit_base_link && $email) {
			$userhash = md5($email.KOOL_ENCRYPTION_KEY);
			$map['###_USERHASH###'] = $userhash;
			$link = FALSE !== strpos($edit_base_link, '?') ? '&' : '?';
			$map['###_EDIT_LINK###'] = $edit_base_link.$link.'hash=e'.$userhash;
			$map['###_DELETE_LINK###'] = $edit_base_link.$link.'hash=d'.$userhash;
		} else {
			$map['###_USERHASH###'] = '';
			$map['###_EDIT_LINK###'] = '';
			$map['###_DELETE_LINK###'] = '';
		}

		$GLOBALS['kOOL']['mailingMarkerMap'][$key] = $map;
	}

	//If email text itself is encoded with quoted-printable then first decode, replace markers and re-encode
	//Otherwise markers might be split (##=\n#_EDIT_LINK###) and not found
	if($qp) {
		$string8bit = imap_qprint($string);
		return imap_8bit(str_replace(array_keys($map), array_values($map), $string8bit));
	} else {
		return str_replace(array_keys($map), array_values($map), $string);
	}
}//ko_mailing_markers()
