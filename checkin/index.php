<?php

header('Content-Type: text/html; charset=ISO-8859-1');

$ko_menu_akt = 'checkin';
$ko_path = '../';

require_once("{$ko_path}inc/ko.inc");
require_once("{$ko_path}checkin/inc/checkin.inc");
require_once("{$ko_path}tracking/inc/tracking.inc");
require_once("{$ko_path}inc/googleCloudPrint/koGoogleCloudPrint.php");

$onload_code = '';
$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

//Get access rights
ko_get_access('tracking');

if (isset($_GET['action'])) {
	$do_action = format_userinput($_GET['action'], 'alphanum+');
} else if (isset($_POST['action'])) {
	$do_action = format_userinput($_POST['action'], 'alphanum+');
} else if ($_SESSION['checkin_user']) {
	$do_action = 'show_checkin';
} else {
	$do_action = 'login';
}

if (!ko_get_setting('tracking_enable_checkin')) $notifier->addError(1);

switch ($do_action) {
	case 'login':
		$trackingId = format_userinput($_GET['t'], 'uint');
		$googlePrinterId = format_userinput($_GET['p'], 'alphanum+');
		$qzPrinterName = format_userinput($_GET['qzp'], 'dir');
		$mode = format_userinput($_GET['m'], 'uint');
		if (!$trackingId) $trackingId = $_SESSION['checkin_tracking_id'];
		if (!$trackingId) {
			$notifier->addError(2);
			$_SESSION['show'] = 'empty';
			break;
		}
		$printer = NULL;
		if ($googlePrinterId) {
			$printers = ko_get_available_google_cloud_printers();
			foreach ($printers as $pr) {
				if ($pr['id'] == $googlePrinterId) {
					$printer = $pr;
					$printer['type'] = 'google';
					break;
				}
			}
			if (!$printer) {
				$notifier->addError(9);
				$_SESSION['show'] = 'empty';
				break;
			}
		} else if($qzPrinterName) {
			$printer = [
				'type' => 'qz_tray',
				'name' => $qzPrinterName,
				'id' => $qzPrinterName,
			];
		}

		$tracking = db_select_data('ko_tracking', "WHERE `id` = {$trackingId}", '*', '', '', TRUE);
		if (!$tracking || $tracking['id'] != $trackingId) {
			$notifier->addError(2);
			$_SESSION['show'] = 'empty';
			break;
		} else if (!$tracking['enable_checkin']) {
			$notifier->addError(3);
			$_SESSION['show'] = 'empty';
			break;
		}

		$today = date('Y-m-d');
		$dates = ko_array_column(ko_tracking_get_dates($tracking, $today, 1, $prev, $next, $prev1, TRUE), 'date');
		if (!in_array($today, $dates)) {
			$notifier->addError(5);
			$_SESSION['show'] = 'empty';
			break;
		}

		$groupFilters = array();
		foreach (explode(',', $tracking['filter']) as $filter) {
			if(strlen($filter) >= 7 && substr($filter, 0, 1) == 'g' && preg_match('/^[g0-9:r,]*$/', $filter)) {
				list($gid, $rid) = explode(':', $filter);
				$gid = ko_groups_decode($filter, 'full_gid');
				$groupFilters[ko_groups_decode($gid, 'group_desc_full')] = $gid;
			}
		}
		if (sizeof($groupFilters) == 0) {
			$notifier->addError(8);
			$_SESSION['show'] = 'empty';
			break;
		}

		$checkinUser = db_select_data('ko_admin', "WHERE `login` = '_checkin_user'", '*', '', '', TRUE);
		if (!$checkinUser || $checkinUser['login'] != '_checkin_user') {
			$notifier->addError(7);
			$_SESSION['show'] = 'empty';
			break;
		}

		if (!$notifier->hasErrors()) {
			$_SESSION['checkin_tracking_id'] = $trackingId;
			$_SESSION['checkin_printer'] = $printer;
			$_SESSION['checkin_mode'] = $mode;

			$_SESSION['ses_userid'] = $checkinUser['id'];
			$_SESSION['show'] = 'login';
		} else {
			$_SESSION['show'] = 'empty';
		}
	break;

	case 'submit_login':
		$tracking = db_select_data('ko_tracking', "WHERE `id` = {$_SESSION['checkin_tracking_id']}", '*', '', '', TRUE);
		$passphrase = $_POST['passphrase'];
		if ($tracking['checkin_admin_pass'] && $tracking['checkin_admin_pass'] == $passphrase) {
			$notifier->addInfo(1);
			$_SESSION['checkin_user'] = 'admin';
			$_SESSION['show'] = 'checkin';
		} else if ($tracking['checkin_guest_pass'] && $tracking['checkin_guest_pass'] == $passphrase) {
			$notifier->addInfo(2);
			$_SESSION['checkin_user'] = 'guest';
			$_SESSION['show'] = 'checkin';
		} else {
			$notifier->addError(4);
		}
	break;

	case 'show_checkin':
		$_SESSION['show'] = 'checkin';
	break;
}


?>

<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php print getLL('checkin_page_title'); ?></title>
<?php
print ko_include_css();
print ko_include_js();

include("{$ko_path}checkin/inc/js-checkin.inc");

if(ko_get_setting('qz_tray_enable')) {
	if(!empty($_SESSION['checkin_user']) && isset($_SESSION['checkin_printer']) && $_SESSION['checkin_printer']['type'] == 'qz_tray') {
?>
	<script src="/inc/qz-tray/rsvp-3.1.0.min.js" charset="UTF-8"></script>
	<script src="/inc/qz-tray/sha-256.min.js" charset="UTF-8"></script>
	<script src="/inc/qz-tray/qz-tray.js" charset="UTF-8"></script>
	<script>
		qz.security.setCertificatePromise(function(resolve,reject) {
			$.ajax({url:'/inc/ajax.php',cache:false,dataType:'text',data:{sesid:'<?= session_id() ?>',action:'getcert'}}).then(resolve,reject);
		});
		qz.security.setSignaturePromise(function(toSign) {
			return function(resolve,reject) {
				$.ajax({url:'/inc/ajax.php',data:{sesid:'<?= session_id() ?>',action:'rsasign',sign:toSign}}).then(resolve,reject);
			};
		});
		var printLabel = (function(qz) {
			var connected = $.Deferred();
			qz.websocket.connect({host:'<?= ko_get_setting('qz_tray_host') ?: 'localhost' ?>'}).then(function() {
				connected.resolve();
			});
			return function(params,options) {
				$.when(connected).then(function() {
					qz.configs.create('<?= $_SESSION['checkin_printer']['id'] ?>',options).print([params]);
				});
			}
		})(qz);
	</script>
<?php
	} else if($do_action == 'login' && $printer['type'] == 'qz_tray') {
?>
	<script>
		var check = new WebSocket("wss://<?= ko_get_setting('qz_tray_host') ?: 'localhost' ?>:8181");
		check.onerror = function() {
			check.onclose = function(e) {
				$(document).ready(function() {
					var backdrop = $('<div>').css({
						position:'fixed',
						top:0,
						left:0,
						right:0,
						bottom:0,
						background:'rgba(0,0,0,0.6)',
						display:'flex',
						'justify-content':'center',
						'align-items':'center',
						'z-index':1000
					}).click(function() {
						backdrop.remove();
					});
					var modal = $('<div>').css({
						padding:'5em',
						background:'#ffffff'
					})
					modal.append($('<h3>').text('<?= getLL('checkin_qztray_printer_offline_title') ?>'));
					if(e.code == 1015) {
						modal.append($('<p>').text('<?= getLL('checkin_qztray_certificate_error_message') ?>'));
						modal.append($('<p>').append($('<a>').attr('href','https://<?= ko_get_setting('qz_tray_host') ?: 'localhost' ?>:8181').text('https://<?= ko_get_setting('qz_tray_host') ?: 'localhost' ?>:8181')));
					} else {
						modal.append($('<p>').text('<?= getLL('checkin_qztray_printer_offline_message') ?>'));
					}
					$('body').append(backdrop.append(modal));
				});
			}
		}
		check.onopen = function() {
			check.close();
		}
	</script>
<?php
	}
}

?>
	<style type="text/css">
::-webkit-input-placeholder {
	color:#777 !important;
}
::-moz-placeholder {
	color:#777 !important;
	opacity:1 !important;
}
:-ms-input-placeholder {
	color:#777 !important;
}
::-ms-input-placeholder {
	color:#777 !important;
}
::placeholder {
	color:#777 !important;
}
#search-bar .input-group .input-group-addon,
#search-bar .input-group .form-control,
#search-bar .input-group button {
	height:36px;
	font-size:16px;
}
	</style>
</head>

<body onload="<?php print $onload_code; ?>" <?= $_SESSION['checkin_user'] ? ' class="'.$_SESSION['checkin_user'].'"' : '' ?>>
<form action="index.php" method="post" name="formular" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="action" id="action" value="">
<input type="hidden" name="id" id="id" value="">
<main id="checkin">

<?php

switch ($_SESSION['show']) {
	case 'empty':
		print '<div class="row" style="padding-top:15px;"><div id="notifications" name="notifications" class="col-md-8 col-md-offset-2"></div></div>';
	break;
	case 'login':
		ko_checkin_form_login();
	break;

	case 'checkin':
		ko_checkin_show_checkin();
	break;
}

$notifier->jsDisplay();

?>

</main>
</form>
</body>
</html>


