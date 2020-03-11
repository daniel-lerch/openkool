<?php

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

$ko_menu_akt = 'checkin';
$ko_path = '../../';

require_once("{$ko_path}inc/ko.inc");
require_once("{$ko_path}checkin/inc/checkin.inc");
require_once("{$ko_path}tracking/inc/tracking.inc");

$onload_code = '';
$notifier = koNotifier::Instance();


//Get access rights
ko_get_access('tracking');


if (isset($_GET['action'])) {
	$do_action = format_userinput($_GET['action'], 'alphanum+');
} else if (isset($_POST['action'])) {
	$do_action = format_userinput($_POST['action'], 'alphanum+');
} else {
	print '';
	exit;
}

switch ($do_action) {
	case 'search':
		if ($_SESSION['checkin_user'] && $_SESSION['checkin_tracking_id']) {
			$query = $_GET['query'];
			$trackingId = $_SESSION['checkin_tracking_id'];

			print 'search-result-container@@@';
			ko_checkin_show_results($trackingId, $query);
			print '<script>hideWait($("#search-btn"));</script>';
		}
	break;

	case 'checkin':
		$ids = ko_array_filter_empty(explode(',', format_userinput($_GET['ids'], 'intlist')));
		$date = ko_checkin_get_date($trackingId);

		$errors = $successes = 0;
		$trackingEntries = array();
		foreach ($ids as $id) {
			$error = ko_checkin_set_tracking_entry($_SESSION['checkin_tracking_id'], $id, $date, $trackingEntry);
			if ($error) {
				$errors++;
			} else {
				$trackingEntries[$id] = $trackingEntry;
				$successes++;
			}
		}

		if (is_array($_SESSION['checkin_printer'])) {
			if (sizeof($trackingEntries) > 0) {
				$filename = ko_checkin_create_labels($trackingEntries, $date, $_SESSION['checkin_tracking_id'], $_SESSION['checkin_printer']['id'], $_SESSION['checkin_printer']['type']);
				$options = ko_checkin_get_print_options($trackingEntries, $date, $_SESSION['checkin_tracking_id'], $_SESSION['checkin_printer']['id'], $_SESSION['checkin_printer']['type']);
				if($_SESSION['checkin_printer']['type'] == 'google') {
					$gcp = koGoogleCloudPrint::Instance();
					$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', $options);

					/*$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', array('page_orientation' => 'L', 'fit_to_page' => 'NO_FITTING', 'media_size' => array('width' => 62000, 'is_continuous' => TRUE)));
					$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', array('page_orientation' => 'L', 'fit_to_page' => 'NO_FITTING', 'media_size' => array('width' => 62000, 'height' => 40000)));
					$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', array('page_orientation' => 'L', 'fit_to_page' => 'FIT_TO_PAGE', 'media_size' => array('width' => 62000, 'is_continuous' => TRUE)));
					$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', array('page_orientation' => 'L', 'fit_to_page' => 'FIT_TO_PAGE', 'media_size' => array('width' => 62000, 'height' => 40000)));
					$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', array('page_orientation' => 'P', 'fit_to_page' => 'NO_FITTING', 'media_size' => array('width' => 62000, 'is_continuous' => TRUE)));
					$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', array('page_orientation' => 'P', 'fit_to_page' => 'NO_FITTING', 'media_size' => array('width' => 62000, 'height' => 40000)));
					$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', array('page_orientation' => 'P', 'fit_to_page' => 'FIT_TO_PAGE', 'media_size' => array('width' => 62000, 'is_continuous' => TRUE)));
					$ok = $gcp->sendPrintToPrinter($_SESSION['checkin_printer']['google_id'], 'kOOL Check-In Label', $filename, 'application/pdf', array('page_orientation' => 'P', 'fit_to_page' => 'FIT_TO_PAGE', 'media_size' => array('width' => 62000, 'height' => 40000)));*/

					if ($ok['status']) {
						$notifier->addInfo(4, '', array(sizeof($trackingEntries), $_SESSION['checkin_printer']['name']));
					} else {
						$notifier->addError(10, '', array($ok['errormessage']));
					}
				} else if($_SESSION['checkin_printer']['type'] == 'qz_tray') {
					print "POST@@@printLabel({type:'pdf',format:'base64',data:'".base64_encode(file_get_contents($filename))."'},".($options ? json_encode($options) : '{}').");@@@";
				}
			}
		} else {
			$notifier->addInfo(3, '', array($successes));
		}

		if ($errors > 0) {
			$notifier->addWarning(1, '', array($errors));
		}

		print 'notifications@@@';
		$notifier->display();

		if($_SESSION['checkin_mode'] == 1) {
			print '@@@search-result-container@@@';
			print ko_checkin_show_results($_SESSION['checkin_tracking_id'], '_all');
			print '<script>hideWait($("#search-btn"));</script>';
		} else {
			print "@@@POST@@@hideWait($('.checkin-selected-btn'));$('#search-input').val('');";
			print '@@@search-result-container@@@<div class="panel panel-default"><div class="panel-body">'.getLL('checkin_label_enter_query').'</div></div>';
		}

	break;

	case 'checkout':
		if($_SESSION['checkin_user'] == 'admin' && $_SESSION['checkin_tracking_id']) {
			$trackingId = $_SESSION['checkin_tracking_id'];
			$personId = format_userinput($_GET['id'],'int');
			ko_checkin_unset_tracking_entry($trackingId,$personId,ko_checkin_get_date($trackingId));

			$person = db_select_data('ko_leute',"WHERE id='".$personId."'",'vorname,nachname','','',true);

			$notifier->addTextInfo(sprintf(getLL('checkin_checkout_notification'),$person['vorname'].' '.$person['nachname']));
		}

		print 'notifications@@@';
		$notifier->display();

		if($_SESSION['checkin_mode'] == 1) {
			print '@@@search-result-container@@@';
			print ko_checkin_show_results($_SESSION['checkin_tracking_id'], '_all');
			print '<script>hideWait($("#search-btn"));</script>';
		} else {
			print "@@@POST@@@hideWait($('.checkin-selected-btn'));$('#search-btn').click();";
		}
	break;
}
