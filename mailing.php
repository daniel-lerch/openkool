<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
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
if(isset($argc) && $argc >= 1) {
	die("This script can't be executed from console anylonger. You have to create a scheduler-task which calls the function 'ko_mailing_main' \n");
}

function ko_mailing_main ($test = false, $mail_id_in = null, $recipient_in = null) {
	global 		$MODULES,$MAILING_PARAMETER, $BASE_PATH,
				  $db_user,$login_id,$access,
				  $domain,$edit_base_link,
				  $done_error_mails,$return_path,$imap,$max_recipients,$sender_email;

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
	if(!in_array('mailing', $MODULES)) return;
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


	// Constants: Mailing status
	define('MAILING_STATUS_OPEN', 1);
	define('MAILING_STATUS_CONFIRMED', 2);
	define('MAILING_STATUS_SENT', 3);

	//Contants: Errors
	define('ERROR_INVALID_GROUP_ID', 1);
	define('ERROR_INVALID_SMALLGROUP_ID', 2);
	define('ERROR_INVALID_SENDER', 3);
	define('ERROR_INVALID_CODE', 4);
	define('ERROR_NO_ALIAS_FOUND', 5);
	define('ERROR_INVALID_RECIPIENT', 6);
	define('ERROR_NON_UNIQUE_ALIAS', 7);
	define('ERROR_TOO_MANY_RECIPIENTS', 8);
	define('ERROR_NO_ACCESS', 9);
	define('ERROR_GROUP_NO_ACCESS', 10);
	define('ERROR_SMALLGROUP_NO_ACCESS', 11);
	define('ERROR_MYLIST_NO_ACCESS', 12);
	define('ERROR_ONLY_ALIAS', 13);
	define('ERROR_LEUTE_NO_ACCESS', 14);
	define('ERROR_CODE_ALREADY_CONFIRMED', 15);
	define('ERROR_GROUP_NO_ACCESS_EMAIL', 16);
	define('ERROR_MODERATION_EMAIL', 17);
	define('ERROR_MYLIST_EMPTY', 18);
	define('ERROR_FILTER_EMPTY', 19);
	define('ERROR_INVALID_GROUP_ROLE_ID', 20);
	define('ERROR_NO_RECIPIENTS', 21);



	/** TESTING
	 * Allows to send a stored mailing email to a specified recipient
	 * Call with php5 mailing.php test ID RECIPIENT
	 * Where ID is the ID of the mailing email from DB table ko_mailing_mails and RECIPIENT is the email address to send this email to
	 */
	if($test) {
		$mail_id = $mail_id_in;
		$recipient = $recipient_in;

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

		//Set return path to sender's email
		$_return_path = ($return_path == 'USER') ? '-f'.$mail['from'] : $return_path;

		$to = '=?ISO-8859-1?Q?'.imap_8bit('Test User').'?='.' <'.$recipient.'>';
		$sent = mail($to,
			ko_mailing_markers($mail['subject'], 1, $recipient),
			'',
			ko_emailtext($mail['header']).ko_emailtext(ko_mailing_markers($mail['body'], 1, $recipient, $qp)),
			$_return_path
		);

		return print_r($sent, true);
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

	$done_mailids = array();
	$done_error_mails = array();


	//Get emails from pop account
	$imap_status = imap_check($imap);
	$num_mails = $imap_status->Nmsgs;
	if($num_mails > 0) {
		//Get mails
		$mails = array();
		$response = imap_fetch_overview($imap,'1:'.$num_mails);
		foreach ($response as $msg) $mails[$msg->msgno] = (array)$msg;

		foreach($mails as $mail) {
			//Get header info
			$header = imap_rfc822_parse_headers(imap_fetchheader($imap, $mail['msgno']));

			//Check message id
			//If one mail has two or more recipients, the same mail will be stored multiple times, so we ignore all but the first one
			//For each mail all recipients will be handled below, so no need to work through all copies
			if(in_array($mail['message_id'], $done_mailids)) {
				imap_delete($imap, $mail['msgno']);
				continue;
			}

			//Get all recipients of this email
			$mail_recipients = array();
			foreach($header->to as $obj) {
				if($obj->host != $domain) continue;  //ignore other hosts
				$mail_recipients[] = $obj->mailbox.'@'.$obj->host;
			}
			if($header->cc) {
				foreach($header->cc as $obj) {
					if($obj->host != $domain) continue;  //ignore other hosts
					$mail_recipients[] = $obj->mailbox.'@'.$obj->host;
				}
			}


			/* TODO: Throw this error in _check_group() if not allowed
			ko_mailing_error(ERROR_INVALID_SENDER, $mail);
			//Delete message
			imap_delete($imap, $mail['msgno']);
			//Store message id
			$done_mailids[] = $mail['message_id'];
			continue;
			*/

			//Check sender email and find corresponding kOOL login
			$login_id = ko_mailing_get_sender_login($mail['from']);
			if($login_id) {
				ko_get_login($login_id, $db_user);
				$no_access = FALSE;

				//Check access rights for found user
				$access = array();
				//Access to the mailing module
				if(!ko_module_installed('mailing', $login_id)) {
					$no_access = TRUE;
				}
				//Access to people module
				ko_get_access('leute', $login_id);
				if(!ko_module_installed('leute', $login_id) || $access['leute']['MAX'] < 1) {
					$no_access = TRUE;
				}
				// unset login_id, so that the following test will only consider the sender_email
				if ($no_access) {
					unset($login_id);
				}
				//Access rights for groups and smallgroups, check will be done further down
				ko_get_access('groups', $login_id);
				ko_get_access('kg', $login_id);
			}
			// continue also with sender email. Maybe it is allowed to send email to it's own group
			$sender_email = $mail['from'];

			$found = FALSE;
			foreach($mail_recipients as $mail_recipient) {
				$error = 0;
				$to = str_replace('@'.$domain, '', $mail_recipient);

				//Check for automatically authorized emails
				$auto_confirmed = FALSE;
				if(FALSE !== strpos($to, '+')) {
					list($to, $auth) = explode('+', $to);
					if(KOOL_ENCRYPTION_KEY != '' && strlen($auth) == 32) {
						$auto_confirmed = md5(date('d').$to.KOOL_ENCRYPTION_KEY) == $auth;
					}
				}

				//Allow sending to groups without moderation. Will be set to TRUE in ko_mailing_check_group()
				$no_mod = FALSE;

				//Find mails sent to noreply (e.g. autoresponders)
				if($to == 'noreply') {
					$found = TRUE;
				}
				//Find confirm emails
				else if(substr($to, 0, strlen('confirm-')) == 'confirm-') {
					$code = substr($to, strlen('confirm-'));
					$error = ko_mailing_check_code($code, $mail2);

					if($error) {
						ko_mailing_error($error, (is_array($mail2) ? $mail2 : $mail), $to);
					} else {
						ko_mailing_mail_confirmed($code);
					}
					$found = TRUE;

				}
				//Find group with id
				else if(1 == preg_match('/^gr([0-9.]*$)/', $to, $m)) {
					list($all, $data) = $m;

					list($gid, $rid) = explode('.', $data);
					$error = ko_mailing_check_group($gid, $rid, $no_mod);

					//Don't allow sender if setting prohibits addresses with no alias
					if(ko_get_setting('mailing_only_alias')) $error = ERROR_ONLY_ALIAS;

					if($error) {
						ko_mailing_error($error, $mail, $to);
					} else {
						$mail['_recipient'] = 'gr'.$gid.($rid ? '.'.$rid : '');
						$use_group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
						$mail['_reply_to'] = $use_group['mailing_reply_to'];
						$modifyRcpts = $use_group['mailing_modify_rcpts'];
						$mail['_to'] = $to.'@'.$domain;
						list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $modifyRcpts);
						$found = TRUE;
					}
				}
				//Find smallgroup with id
				else if(1 == preg_match('/^sg([0-9]{4})([a-zA-Z.]*)$/', $to, $m)) {
					list($all, $sgid, $rid) = $m;

					$error = ko_mailing_check_smallgroup($sgid, $rid);

					//Don't allow sender if setting prohibits addresses with no alias
					if(ko_get_setting('mailing_only_alias')) $error = ERROR_ONLY_ALIAS;

					if($error) {
						ko_mailing_error($error, $mail, $to);
					} else {
						$mail['_recipient'] = 'sg'.$sgid.($rid?'.'.$rid:'');
						list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail);
						$found = TRUE;
					}
				}
				//My List
				else if($to == 'ml') {
					$error = ko_mailing_check_mylist();

					if($error) {
						ko_mailing_error($error, $mail, $to);
					} else {
						$mail['_recipient'] = 'ml';
						list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail);
						$found = TRUE;
					}
				}
				//Find filter preset with id
				else if(1 == preg_match('/^fp([0-9]*$)/', $to, $m)) {
					list($all, $data) = $m;

					$fid = intval($data);
					$error = ko_mailing_check_filter($fid);

					//Don't allow sender if setting prohibits addresses with no alias
					if(ko_get_setting('mailing_only_alias')) $error = ERROR_ONLY_ALIAS;

					if($error) {
						ko_mailing_error($error, $mail, $to);
					} else {
						$mail['_recipient'] = 'fp'.$fid;
						list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail);
						$found = TRUE;
					}
				}
				//Find mailing alias
				else {
					//Find group or small group with this alias
					$groups = db_select_data('ko_groups', "WHERE LOWER(`mailing_alias`) = '".mysql_real_escape_string(strtolower($to))."'");
					$smallgroups = db_select_data('ko_kleingruppen', "WHERE LOWER(`mailing_alias`) = '".mysql_real_escape_string(strtolower($to))."'");
					$filters = db_select_data('ko_userprefs', "WHERE LOWER(`mailing_alias`) = '".mysql_real_escape_string(strtolower($to))."'");

					$num_found = sizeof($groups)+sizeof($smallgroups)+sizeof($filters);
					if($num_found == 0) {
						ko_mailing_error(ERROR_NO_ALIAS_FOUND, $mail, $to);
					} else if($num_found > 1) {
						ko_mailing_error(ERROR_NON_UNIQUE_ALIAS, $mail, $to);
					} else {
						if(sizeof($groups) == 1) {
							$group = array_shift($groups);
							$error = ko_mailing_check_group($group['id'], '', $no_mod);
							if($error) {
								ko_mailing_error($error, $mail, $to);
							} else {
								$mail['_recipient'] = 'gr'.$group['id'];
								$mail['_reply_to'] = $group['mailing_reply_to'];
								$mail['_to'] = $to.'@'.$domain;
								$modifyRcpts = $group['mailing_modify_rcpts'];
								list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail, $modifyRcpts);
								$found = TRUE;
							}
						} else if(sizeof($smallgroups) == 1) {
							$sg = array_shift($smallgroups);
							$error = ko_mailing_check_smallgroup($sg['id']);
							if($error) {
								ko_mailing_error($error, $mail, $to);
							} else {
								$mail['_recipient'] = 'sg'.$sg['id'];
								list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail);
								$found = TRUE;
							}
						} else if(sizeof($filters) == 1) {
							$fp = array_shift($filters);
							$error = ko_mailing_check_filter($fp['id']);
							if($error) {
								ko_mailing_error($error, $mail, $to);
							} else {
								$mail['_recipient'] = 'fp'.$fp['id'];
								list($new_id, $new_code) = ko_mailing_store_moderation($imap, $mail);
								$found = TRUE;
							}
						}
					}
				}

				if($found) {
					//Auto confirm email if auth check above passed
					if(($auto_confirmed && $new_code) || $no_mod) {
						ko_mailing_mail_confirmed($new_code);
					} else if($new_code) {
						$error = ko_mailing_send_moderation_mail($new_id, $mail, $db_user, $sender_email);
						if($error) ko_mailing_error($error, $mail, $to);
					}
				}

			}//foreach(mail_recipients)

			//None of the email addresses with $domain have been recognized and processed: Return failure notice
			if(!$found) {
				ko_mailing_error(ERROR_INVALID_RECIPIENT, $mail);
			}

			//Delete message after it has been processed
			imap_delete($imap, $mail['msgno']);

			//Store message id, so it won't be processed again (two recipients generate 2 emails each with both recipients)
			$done_mailids[] = $mail['message_id'];

		}//foreach(mails as mail)
	}

//Close connection and expunge
	imap_close($imap, CL_EXPUNGE);


	require($ko_path . 'inc/class.rawSmtpMailer.php');
	$mailer = new RawSmtpMailer(true);


	//Check db for mails to be sent

	$mails = db_select_data('ko_mailing_mails', "WHERE `status` = '".MAILING_STATUS_CONFIRMED."'");
	foreach($mails as $mail) {
		$done = $done_names = array();

		//Find quoted-printable in header
		if(FALSE !== strpos(strtolower($mail['header']), 'content-transfer-encoding: quoted-printable')) $qp = TRUE;
		else $qp = FALSE;
		//Find utf-8 encoding. If set then encode recipient's name
		//if(FALSE !== strpos(strtolower($mail['header']), 'charset=utf-8')) $utf8 = TRUE;
		//else $utf8 = FALSE;

		//Get next recipients and send emails
		$recipients = db_select_data('ko_mailing_recipients', "WHERE `mail_id` = '".$mail['id']."'", '*', '', 'LIMIT 0,'.$mails_per_cycle);

		//Set return path to sender's email
		$_return_path = ($return_path == 'USER') ? '-f'.$mail['from'] : $return_path;

		foreach($recipients as $rec) {
			if(!$rec['id']) continue;
			if ($mail['modify_rcpts']) {
				$to = "To: ".mb_encode_mimeheader($rec['name'], 'UTF-8', 'Q')." <".$rec['email'].">" . CRLF;
			}
			else {
				$to = "";
			}
			$subject = "Subject: " . ko_mailing_markers($mail['subject'], $rec['leute_id'], $rec['email']) . CRLF;
			$bulkHeader = ($bulk_header === true ? 'Precedence: bulk' . CRLF : '');
			$log_to = $rec['name']." (".$rec['email'].")";
			$rcpt = $rec['email'];
			$message = ko_emailtext(trim($mail['header'])).$to.$bulkHeader.$subject.CRLF.ko_emailtext(ko_mailing_markers($mail['body'], $rec['leute_id'], $rec['email'], $qp));
			$sender = $mail['from'];

			$mailer->removeAddresses();

			$mailer->setSender($sender);
			$mailer->addAddress($rcpt);
			$mailer->setMessage($message);
			try {
				$mailer->send();
				$done[] = $rec['id'];
				$done_names[] = $log_to;

			} catch (Exception $e) {
				ko_log('mailing_smtp_error', $e->getMessage());
			}

		}

		//Delete done recipients
		if(sizeof($done) > 0) db_delete_data('ko_mailing_recipients', "WHERE `id` IN ('".implode("','", $done)."')");

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




	//Check for old non-confirmed mails and delete them
	$limit = add2date(date('Y-m-d'), 'day', '-5', TRUE).' 00:00:00';
	$old_mails = db_select_data('ko_mailing_mails', "WHERE `status` = '".MAILING_STATUS_OPEN."' AND `crdate` < '".$limit."'");
	foreach($old_mails as $mail) {
		$log = '';
		$logcols = array('id', 'crdate', 'recipient', 'from', 'subject');
		foreach($logcols as $c) $log .= (getLL('mailing_header_'.$c) ? getLL('mailing_header_'.$c) : $c).': '.$mail[$c].', ';
		db_insert_data('ko_log', array('type' => 'mailing_delete_old', 'comment' => substr($log, 0, -2), 'user_id' => $mail['user_id'], 'date' => date('Y-m-d H:i:s')));
		db_delete_data('ko_mailing_mails', "WHERE `id` = '".$mail['id']."'");
	}

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
		"WHERE ($where_email LOWER(a.email) = '".mysql_real_escape_string($from)."') AND (a.disabled = '0' OR a.disabled = '')",
		"a.id AS id");
	$login = array_shift($logins);
	return $login['id'];
}//ko_mailing_get_sender_login()




/**
 * Mark mail as confirmed and create recipient entries for all recipients in queue
 * @param $code code of th email that was confirmed
 */
function ko_mailing_mail_confirmed($code) {
	global $db_user;

	$mail = db_select_data('ko_mailing_mails', "WHERE `code` = '$code' AND `status` = '".MAILING_STATUS_OPEN."'", '*', '', '', TRUE);

	//Get recipients
	$recipients = ko_mailing_get_recipients($mail['recipient']);

	//Create db entries
	$done_emails = array();
	foreach($recipients as $r) {
		ko_get_leute_email($r, $emails);
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
	$log = $mail['id'].': '.'Subject: '.$mail['subject'].', From: '.$mail['from'].', To: '.$mail['recipient'].', Recipients: '.sizeof($recipients);
	db_insert_data('ko_log', array('type' => 'mailing_confirmed', 'comment' => $log, 'user_id' => $db_user['id'], 'date' => date('Y-m-d H:i:s')));
}//ko_mailing_mail_confirmed()




/**
 * Return a list of entries from ko_leute who are recipients of the given mailinglist
 * @param $rec sgXXXX[.Y], grXXXXXX.YYYYYY, fpX or ml
 */
function ko_mailing_get_recipients($rec) {
	global $access, $login_id, $sender_email;

	$mode = substr($rec, 0, 2);
	$data = substr($rec, 2);
	$_recipients = array();
	$allow = false;

	switch($mode) {
		case 'gr':
			$parts = explode('.', $data);
			$gid = array_shift($parts);
			$rid = array_shift($parts);
			$group = 'g'.$gid.($rid ? ':r'.$rid : '');
			$where = "WHERE `groups` REGEXP '$group' AND `deleted` = '0' AND `hidden` = '0'";
			$_recipients = db_select_data('ko_leute', $where);
			$g = db_select_data('ko_groups', 'where id = ' . $gid, '*', '', '', TRUE, TRUE);
			ko_mailing_check_sender_email_access($g, $sender_email, $allow, $allow_without_mod);
		break;

		case 'ml':
			$ids = unserialize(ko_get_userpref($login_id, 'leute_my_list'));
			if(sizeof($ids) > 0) {
				$_recipients = db_select_data('ko_leute', "WHERE `id` IN ('".implode("','", $ids)."') AND `deleted` = '0' AND `hidden` = '0'");
			}
		break;

		case 'sg':
			$parts = explode('.', $data);
			$sgid = array_shift($parts);
			$rid = array_shift($parts);
			$_recipients = db_select_data('ko_leute', "WHERE `smallgroups` REGEXP '".$sgid.($rid?':'.$rid:'')."' AND `deleted` = '0' AND `hidden` = '0'");
		break;

		case 'fp':
			$fid = intval($data);
			$filterPreset = db_select_data('ko_userprefs', "WHERE `type` = 'filterset' AND `id` = '$fid'", '*', '', '', TRUE);
			$filter = unserialize($filterPreset['value']);

			if(!$filter) $where = 'AND 1=2';
			else apply_leute_filter($filter, $where);

			$_recipients = db_select_data('ko_leute', "WHERE 1=1 ".$where);
		break;
	}

	//Perform tests
	$recipients = array();
	foreach($_recipients as $r) {
		//Check for access to this single address
		if($login_id > 0 && $access['leute']['ALL'] < 1 && $access['leute'][$r['id']] < 1 && !$allow) continue;
		//Check for valid email
		if(FALSE === ko_get_leute_email($r, $emails)) continue;

		$recipients[] = $r;
	}

	return $recipients;
}//ko_mailing_get_recipients()




/**
 * Retrieve a human readable name of a given recipient (gr000001, sg000001, ml)
 */
function ko_mailing_get_rec_name($rec) {
	global $all_groups, $db_user;

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
			return $db_user['login']."'s ".getLL('mailing_mylist');
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
function ko_mailing_error($error, $mail, $to='') {
	global $domain, $db_user, $done_error_mails, $max_recipients, $return_path;

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
		$message .= "\n\n".ko_mailing_summary($mail);

		ko_send_mail('noreply@'.$domain, $mail['from'], getLL('mailing_errormail_subject'), $message);
	}//if(check_email(from))


	//Create log entry
	$log = 'ERROR '.$error.' '.getLL('mailing_error_'.$error).' - ';
	foreach($mail as $k => $v) {
		if($k && $v) $log .= $k.': '.$v.', ';
	}
	if(substr($log, 0, -2) == ', ') $log = substr($log, 0, -2);
	db_insert_data('ko_log', array('type' => 'mailing_error', 'comment' => $log, 'user_id' => $db_user['id'], 'date' => date('Y-m-d H:i:s')));
}//ko_mailing_error()




function ko_mailing_store_moderation($imap, $mail, $modifyRcpts = true) {
	global $db_user, $sender_email;


	//Get email body and create email code
	$body = imap_body($imap, $mail['msgno']);
	$code = md5(uniqid(mt_rand(), true));

	//Get header and delete some entries
	$header = imap_fetchheader($imap, $mail['msgno']);
	if ($modifyRcpts) {
		$header = preg_replace('/(\n|^)To: (.*)(\n\s+(.*))*\n/i', '$1', $header);
		$header = preg_replace('/(\n|^)Cc: (.*)(\n\s+(.*))*\n/i', '$1', $header);
	}
	$header = preg_replace('/(\n|^)Bcc: (.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)Subject: (.*)(\n\s+(.*))*\n/i', '$1', $header);
	$header = preg_replace('/(\n|^)Delivered-To: (.*)(\n\s+(.*))*\n/i', '$1', $header);
	if($mail['_reply_to']) {
		//Remove Reply-To
		$header = preg_replace('/(\n|^)Reply-To: (.*)(\n\s+(.*))*\n/i', '$1', $header);
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
		$header .= 'Reply-To: '.$replyTo."\n";
	}

	//Create db entry for email
	$entry = array();
	$entry['body'] = $body;
	$entry['header'] = $header;
	$entry['status'] = MAILING_STATUS_OPEN;
	$entry['crdate'] = date('Y-m-d H:i:s');
	$entry['code'] = $code;
	$entry['subject'] = $mail['subject'];
	$entry['from'] = $mail['from'];
	$entry['user_id'] = $db_user['id'];
	$entry['sender_email'] = $sender_email;
	$entry['recipient'] = $mail['_recipient'];
	$entry['modify_rcpts'] = $modifyRcpts;
	$new_id = db_insert_data('ko_mailing_mails', $entry);

	//Create log entry
	$log = $new_id.': Subject: '.$mail['subject'].', From: '.$mail['from'].', To: '.$mail['to'].', Size: '.$mail['size']. ', ModifyRecipients: ' . $modifyRcpts;
	db_insert_data('ko_log', array('type' => 'mailing_new', 'comment' => $log, 'user_id' => $db_user['id'], 'date' => date('Y-m-d H:i:s')));

	return array($new_id, $code);
}//ko_mailing_store_moderation()





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
function ko_mailing_send_moderation_mail($mid, $mail, $db_user, $sender_email) {
	global $domain, $return_path;

	if(!$mid) return ERROR_MODERATION_EMAIL;

	$mailing = db_select_data('ko_mailing_mails', "WHERE `id` = '$mid'", '*', '', '', TRUE);
	if(!$mailing['id'] || $mailing['id'] != $mid || $mailing['status'] != MAILING_STATUS_OPEN) return ERROR_MODERATION_EMAIL;

	//Get email of logged in user (for moderation email)
	if($db_user['id']) {
		$person = ko_get_logged_in_person($db_user['id']);
		if(check_email($person['email'])) $to = array($person['email']);
	}
	//If sender_email given then get moderators for the recipient group
	else if($sender_email) {
		$gid = format_userinput(str_replace('gr', '', $mail['_recipient']), 'uint');
		$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
		if(!$group['id'] || $group['id'] != $gid) return ERROR_INVALID_GROUP_ID;

		//Get moderators for this group
		$to = ko_mailing_get_moderators_by_group($group);
	}
	else {
		return ERROR_MODERATION_EMAIL;
	}

	//If no moderator then send email back to sender with error message
	if(sizeof($to) == 0) {
		return ERROR_MODERATION_EMAIL;
	}

	//Send moderation emails
	$from = array('confirm-'.$mailing['code'].'@'.$domain => '');
	$from_email = 'confirm-'.$mailing['code'].'@'.$domain;
	$subject = getLL('mailing_confirm_subject').': '.$mail['subject'];
	$message = sprintf(getLL('mailing_confirm_text'), $from_email)."\n\n".ko_mailing_summary($mail, $mailing['body']);
	foreach($to as $t) {
		if(!check_email($t)) continue;
		ko_send_mail($from, $t, $subject, $message);
	}

	return 0;
}//ko_mailing_send_moderation_mail()




function ko_mailing_summary($mail, $body='') {
	global $imap;

	$summary = '';


	//Get name of recipient (group or other) for a nicer display
	$rec_name = ko_mailing_get_rec_name($mail['_recipient']);
	if($rec_name) $summary .= getLL('mailing_rec_name').': '.$rec_name."\n";

	//Add number of recipients
	$recipients = ko_mailing_get_recipients($mail['_recipient']);
	$summary .= getLL('mailing_number_of_recipients').': '.sizeof($recipients)."\n";

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
function ko_mailing_check_group($gid, $rid, &$no_mod) {
	global $max_recipients, $access, $login_id, $sender_email;

	//Default to moderation
	$no_mod = FALSE;
	$no_mod_l = false;
	$no_mod_s = false;

	//Check for correct gid
	if(!$gid || strlen($gid) != 6) return ERROR_INVALID_GROUP_ID;
	for($i=0; $i<strlen($gid); $i++) {
		if(!in_array(substr($gid, $i, 1), array(0,1,2,3,4,5,6,7,8,9))) return ERROR_INVALID_GROUP_ID;
	}

	//Check role id if given
	if($rid) {
		if(strlen($rid) != 6) return ERROR_INVALID_GROUP_ROLE_ID;
		for($i=0; $i<strlen($rid); $i++) {
			if(!in_array(substr($rid, $i, 1), array(0,1,2,3,4,5,6,7,8,9))) return ERROR_INVALID_GROUP_ROLE_ID;
		}
	}

	//Check for valid group in DB
	$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
	if(!$group['id'] || $group['id'] != $gid) return ERROR_INVALID_GROUP_ID;
	if($rid && !in_array($rid, explode(',', $group['roles']))) return ERROR_INVALID_GROUP_ID;

	$no_group_access_error_l = FALSE;
	$no_group_access_error_s = FALSE;

	//Check for access by login
	if($login_id > 0) {
		if($access['groups']['ALL'] < 1 && $access['groups'][$gid] < 1) $no_group_access_error_l = TRUE;

		if($group['mailing_mod_logins'] > 0) $no_mod_l = TRUE;
	}
	else {
		$no_group_access_error_l = TRUE;
	}

	//Check for access by sender_email
	ko_mailing_check_sender_email_access($group, $sender_email, $allow, $no_mod_s);
	$no_group_access_error_s = !$allow;

	if ($no_group_access_error_l && $no_group_access_error_s) {
		return ERROR_GROUP_NO_ACCESS_EMAIL;
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
	$recipients = ko_mailing_get_recipients('gr'.$gid.($rid ? '.'.$rid : ''));
	if(sizeof($recipients) <= 0) return ERROR_NO_RECIPIENTS;
	if($max_recipients > 0 && sizeof($recipients) > $max_recipients) return ERROR_TOO_MANY_RECIPIENTS;

	return 0;
}//ko_mailing_check_group()


/**
 * @param $group                        -> the recipient group
 * @param $sender_email the sender's    -> email address
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

	$where = '';
	foreach($LEUTE_EMAIL_FIELDS as $field) {
		$where[] = " `$field` = '".mysql_real_escape_string($sender_email)."' ";
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
function ko_mailing_check_mylist() {
	global $max_recipients, $access;

	//Check for access
	if($access['leute']['MAX'] < 1) return ERROR_MYLIST_NO_ACCESS;

	//Check for recipients
	$recipients = ko_mailing_get_recipients('ml');
	if(sizeof($recipients) <= 0) return ERROR_MYLIST_EMPTY;
	if($max_recipients > 0 && sizeof($recipients) > $max_recipients) return ERROR_TOO_MANY_RECIPIENTS;

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
function ko_mailing_check_filter($fid) {
	global $max_recipients, $access;

	//Check for access
	if($access['leute']['MAX'] < 1) return ERROR_MYLIST_NO_ACCESS;

	//Check for recipients
	$recipients = ko_mailing_get_recipients('fp'.$fid);
	if(sizeof($recipients) <= 0) return ERROR_FILTER_EMPTY;
	if($max_recipients > 0 && sizeof($recipients) > $max_recipients) return ERROR_TOO_MANY_RECIPIENTS;

	return 0;
}//ko_mailing_check_mylist()




/**
 * Check for MyList as recipient
 *
 * - Check for correct small group id and role
 * - Check for access to people module
 * - Check for number of recipients to be greater than 0 and smaller than $max_recipients
 *
 * returns 0 if OK or error code if not OK
 */
function ko_mailing_check_smallgroup($sgid, $rid) {
	global $SMALLGROUPS_ROLES, $max_recipients, $access, $login_id;

	//Check for correct sgid
	if(!$sgid || strlen($sgid) != 4) return ERROR_INVALID_SMALLGROUP_ID;
	for($i=0; $i<strlen($sgid); $i++) {
		if(!in_array(substr($sgid, $i, 1), array(0,1,2,3,4,5,6,7,8,9))) return ERROR_INVALID_SMALLGROUP_ID;
	}

	//Check for access
	if($access['kg']['ALL'] < 1 || ($access['kg']['ALL'] < 2 && !in_array($sgid, kg_get_users_kgid($login_id)))) return ERROR_SMALLGROUP_NO_ACCESS;

	//Check role id if given
	if($rid) {
		if(strlen($rid) != 1) return ERROR_INVALID_SMALLGROUP_ID;
		if(!in_array($rid, $SMALLGROUPS_ROLES)) return ERROR_INVALID_SMALLGROUP_ID;
	}

	//Check for valid smallgroup in DB
	$sg = db_select_data('ko_kleingruppen', "WHERE `id` = '$sgid'", 'id', '', '', TRUE);
	if(!$sg['id'] || $sg['id'] != $sgid) return ERROR_INVALID_SMALLGROUP_ID;

	//Check for recipients
	$recipients = ko_mailing_get_recipients('sg'.$sgid.($rid?'.'.$rid:''));
	if(sizeof($recipients) <= 0) return ERROR_INVALID_SMALLGROUP_ID;
	if($max_recipients > 0 && sizeof($recipients) > $max_recipients) return ERROR_TOO_MANY_RECIPIENTS;

	return 0;
}//ko_mailing_check_smallgroup()





function ko_mailing_check_code($code, &$mail2) {
	//Check for valid md5 hash
	if(strlen($code) != 32) return ERROR_INVALID_CODE;
	for($i=0; $i<strlen($code); $i++) {
		if(!in_array(substr($code, $i, 1), array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f'))) return ERROR_INVALID_CODE;
	}

	//Check db for mail with this code
	$mail = db_select_data('ko_mailing_mails', "WHERE `code` = '$code' AND `status` = '".MAILING_STATUS_OPEN."'", '*', '', '', TRUE);
	if(!$mail['id'] || $mail['code'] != $code) {
		$mail2 = db_select_data('ko_mailing_mails', "WHERE `code` = '$code'", '*', '', '', TRUE);
		if($mail2['status'] == MAILING_STATUS_CONFIRMED || $mail2['status'] == MAILING_STATUS_SENT) {
			return ERROR_CODE_ALREADY_CONFIRMED;
		} else {
			return ERROR_INVALID_CODE;
		}
	}

	return 0;
}//ko_mailing_check_code()




/**
 * Replaces markers in email part for each recipient
 * @param string $string The string where the replacing should happen (Usually subject or email text)
 * @param int $leute_id The ID of DB.ko_leute for the current recipient
 * @param string $email The email address of the current recipient. Must be given separately as it has been specified by ko_get_leute_email()
 *                      The hash code needs to be created with the recipients email address
 * @param boolean $qp Set to true to add new content in email part as quoted-printable text (needed if email itself is quoted-printable encoded
 * @return string New string with all markers replaced
 */
function ko_mailing_markers($string, $leute_id, $email, $qp=FALSE) {
	global $edit_base_link;

	ko_get_person_by_id($leute_id, $p);
	if(!$p['id'] || $p['id'] != $leute_id) return $string;

	$map = array();
	foreach($p as $key => $value) {
		$map['###'.strtoupper($key).'###'] = $value;
	}
	$gender = $p['geschlecht'];
	if(!$gender) {
		if($p['anrede'] == 'Herr') $gender = 'm';
		else if($p['anrede'] == 'Frau') $gender = 'w';
	}
	$map['###_SALUTATION###'] = getLL('mailing_salutation_'.$gender);
	$map['###_SALUTATION_FORMAL###'] = getLL('mailing_salutation_formal_'.$gender);

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

	//kOOL ID to be used in e.g. kool_groupsusbscribe
	$map['###_KID###'] = $leute_id.'p'.substr(md5($leute_id.KOOL_ENCRYPTION_KEY), 0, 10);

	//If email text itself is encoded with quoted-printable then first decode, replace markers and re-encode
	//Otherwise markers might be split (##=\n#_EDIT_LINK###) and not found
	if($qp) {
		$string8bit = imap_qprint($string);
		return imap_8bit(str_replace(array_keys($map), array_values($map), $string8bit));
	} else {
		return str_replace(array_keys($map), array_values($map), $string);
	}
}//ko_mailing_markers()

?>
