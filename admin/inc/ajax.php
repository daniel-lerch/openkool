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

//Set session id from GET (session will be started in ko.inc)
if(!isset($_GET["sesid"])) exit;
if(FALSE === session_id($_GET["sesid"])) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

error_reporting(E_ALL);
$ko_menu_akt = 'admin';
$ko_path = "../../";
require($ko_path."inc/ko.inc");
$ko_path = "../";

array_walk_recursive($_GET,'utf8_decode_array');

//Get access rights
ko_get_access('admin');

//Include KOTA for sms log
ko_include_kota(array('_ko_sms_log', '_ko_telegram_log', 'ko_log', 'ko_admin', 'ko_labels', 'ko_pdf_layout', 'ko_vesr', 'ko_detailed_person_exports'));

// Plugins einlesen:
$hooks = hook_include_main("admin");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

require($BASE_PATH."admin/inc/admin.inc");

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);


if(isset($_GET) && isset($_GET["action"])) {
	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

	switch($action) {
		case 'regeneratecamtkeys':
			$rsa = new \phpseclib\Crypt\RSA();
			$rsa->setPublicKeyFormat(\phpseclib\Crypt\RSA::PUBLIC_FORMAT_OPENSSH);
			$result = $rsa->createKey(4096);
			extract($rsa->createKey(4096));
			ko_set_setting('camt_import_private_key', $privatekey);
			ko_set_setting('camt_import_public_key', $publickey);
			print 'POST@@@';
			print '$(\'[name="txt_camt_import_private_key"]\').val("'.str_replace(array("\n", "\r"), array('\\n', ""), $privatekey).'");';
			print '$(\'[name="txt_camt_import_public_key"]\').val("'.str_replace(array("\n", "\r"), array('\\n', ""), $publickey).'");';
		break;

		case 'setsortlogins':
			if($access['admin']['MAX'] < 5) break;

			$_SESSION['sort_logins'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			$_SESSION['sort_logins_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

			print 'main_content@@@';
			ko_set_logins_list();
		break;

		case "setsortlog":
			if($access['admin']['MAX'] < 4) break;

			$_SESSION["sort_logs"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_logs_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			ko_show_logs();
		break;


		case "setstart":
			if($_SESSION['show'] == 'show_logins' || $_SESSION['show'] == 'show_sms_log' || $_SESSION['show'] == 'show_telegram_log') {
				if($access['admin']['MAX'] < 5) break;

				//Set list start
				if(isset($_GET['set_start'])) {
					$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
				}
				//Set list limit
				if(isset($_GET['set_limit'])) {
					$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
					ko_save_userpref($_SESSION['ses_userid'], 'show_limit_logins', $_SESSION['show_limit']);
				}

				print "main_content@@@";
				if($_SESSION['show'] == 'show_sms_log') {
					ko_show_sms_log();
				} else if($_SESSION['show'] == 'show_telegram_log') {
						ko_show_telegram_log();
				} else {
					ko_set_logins_list();
				}
			}
			else if($_SESSION['show'] == 'show_logs') {
				if($access['admin']['MAX'] < 4) break;

				//Set list start
				if(isset($_GET['set_start'])) {
					$_SESSION['show_logs_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
				}
				//Set list limit
				if(isset($_GET['set_limit'])) {
					$_SESSION['show_logs_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
					ko_save_userpref($_SESSION['ses_userid'], 'show_limit_logs', $_SESSION['show_logs_limit']);
				}

				print "main_content@@@";
				ko_show_logs();
			}
		break;


		case "ablelogin":
			if($access['admin']['MAX'] < 4) break;

			$id = format_userinput($_GET["id"], "uint");
			if($id == ko_get_root_id()) {
				print 'ERROR@@@'.getLL('error_admin_disable_root');
				break;
			}
			if($id == ko_get_guest_id()) {
				print 'ERROR@@@'.getLL('error_admin_disable_guest');
				break;
			}
			if($id == $_SESSION['ses_userid']) {
				print 'ERROR@@@'.getLL('error_admin_disable_self');
				break;
			}

			if($_GET["mode"] == "enabled") {
				$orig_hash = db_select_data("ko_admin", "WHERE `id` = '$id'", "login,disabled", "", "", TRUE);
				$data = array("password" => $orig_hash["disabled"], "disabled" => "");
				ko_log('enable_login', 'ID: '.$id.', '.$orig_hash['login']);
			} else if($_GET["mode"] == "disabled") {
				$orig_hash = db_select_data("ko_admin", "WHERE `id` = '$id'", "login,password", "", "", TRUE);
				$data = array("password" => md5($orig_hash), "disabled" => $orig_hash["password"]);
				ko_log('disable_login', 'ID: '.$id.', '.$orig_hash['login']);
			} else break;

			db_update_data("ko_admin", "WHERE `id` = '$id'", $data);

			print "main_content@@@";
			ko_set_logins_list();
		break;

		case "paymentDetails":

			$file = $_GET['file'];
			if(!file_exists($BASE_PATH.$_GET['file'])) {
				throw new \Exception('the file '.htmlspecialchars($_GET['file']).' does not exist');
			}
			$ext = strtolower(substr($file,-4));
			if($ext == '.xml') {
				$vesrType = 'camt';
			} else if($ext == '.v11') {
				$vesrType = 'v11';
			} else {
				throw new \Exception('the given file is neither a camt nor a v11 file');
			}

			if($_GET['download'] == 'raw') {
				header('Content-Description: File Transfer');
				header('Content-Type: '.($vesrType == 'camt' ? 'application/xml' : 'text/plain'));
				header('Content-Disposition: attachment; filename="'.basename($file).'";');
				header('Content-Length: '.filesize($BASE_PATH.$file));
				readfile($BASE_PATH.$file);
				exit;
			}

			if($vesrType == 'camt') {

				$reader = new LPC\LpcEsr\CashManagement\OfflineReader;

				$processor = new \LPC\LpcEsr\CashManagement\koProcessor;
				$processor->setReportOnly(true);
				$reader->registerProcessor($processor);

				$reader->readOne($BASE_PATH.$file);

				$errorRows = [];
				foreach($processor->getProcessedData() as $processed) {
					if($processed['status'] != 'ok') {
						$errorRows[] = $processed['row'];
					}
				}

				if($_GET['download'] != 'pdf') {

					ko_vesr_camt_overview($processor->getDoneTotal(),$processor->getDoneRows());

					if($errorRows) {
						ko_include_kota(array('ko_vesr_camt'));

						echo '<h2>'.getLL('payment_list_not_mapped_title').'</h2>';

						$list = new kOOL_listview();
						$list->init('admin', 'ko_vesr_camt', array(), 1, 1000);
						$list->setTitle('');
						$list->setSort(FALSE);
						$list->setStats(count($errorRows), '', '', '', TRUE);
						$list->disableMultiedit();
						$list->disableHeader();

						echo $list->render($errorRows, 'html_fetch');
					}

					$fileLink = 'inc/ajax.php?'.http_build_query([
						'action' => 'paymentDetails',
						'file' => $file,
						'download' => 'raw',
						'sesid' => session_id(),
					]);
					echo '<a href="'.$fileLink.'" class="btn btn-default" download><i class="fa fa-download"></i> '.getLL('download_camt_file').'</a> ';
					$pdfLink = 'inc/ajax.php?'.http_build_query([
						'action' => 'paymentDetails',
						'file' => $file,
						'download' => 'pdf',
						'sesid' => session_id(),
					]);
					echo '<a href="'.$pdfLink.'" class="btn btn-default" download><i class="fa fa-file-pdf-o"></i> '.getLL('download_as_pdf').'</a>';
				}

			} else if($vesrType == 'v11') {

				ko_vesr_import($BASE_PATH.$file,$data,$done,true);

				$errors = array();
				foreach(array_values($done) as $d) {
					$errors = array_merge($errors, $d);
				}
				unset($errors['ok']);
				$errorRows = array();
				foreach(array_values($errors) as $e) {
					$errorRows = array_merge($errorRows, $e);
				}

				if($_GET['download'] != 'pdf') {
					ko_vesr_v11_overview($data,$done);

					if($errorRows) {
						ko_include_kota(array('ko_vesr'));

						echo '<h2>'.getLL('payment_list_not_mapped_title').'</h2>';

						$list = new kOOL_listview();
						$list->init('admin', 'ko_vesr', array(), 1, 1000);
						$list->setTitle('');
						$list->setSort(FALSE);
						$list->setStats(count($errorRows), '', '', '', TRUE);
						$list->disableMultiedit();
						$list->disableHeader();

						echo $list->render($errorRows, 'html_fetch');
					}

					$fileLink = 'inc/ajax.php?'.http_build_query([
						'action' => 'paymentDetails',
						'file' => $file,
						'download' => 'raw',
						'sesid' => session_id(),
					]);
					echo '<a href="'.$fileLink.'" class="btn btn-default" download><i class="fa fa-download"></i> '.getLL('download_v11_file').'</a> ';
					$pdfLink = 'inc/ajax.php?'.http_build_query([
						'action' => 'paymentDetails',
						'file' => $file,
						'download' => 'pdf',
						'sesid' => session_id(),
					]);
					echo '<a href="'.$pdfLink.'" class="btn btn-default" download><i class="fa fa-file-pdf-o"></i> '.getLL('download_as_pdf').'</a>';
				}
			}

			if($_GET['download'] == 'pdf') {
				$vesrFiles = [basename($file)];
				if($vesrType == 'camt') {
					$done = $processor->getDoneRows();
					$total = $processor->getDoneTotal();
				} else {
					$total = $data['totals'];
					$total['total'] = $data['total'];
				}
				$pdf = ko_vesr_create_reportattachment($vesrFiles,$total,$done,$vesrType,$errorRows);
				$pdf->Output(pathinfo($file,PATHINFO_FILENAME).'.pdf','D');
				exit;
			}

		break;
	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
