<?php

header('Content-Type: text/html; charset=ISO-8859-1');

$ko_path = "../";
$ko_menu_akt = "subscription";

include($ko_path . "inc/ko.inc");
require_once($ko_path.'subscription/inc/Form.php');
require_once($ko_path.'subscription/inc/FormException.php');
require_once($ko_path.'subscription/inc/subscription.inc');


try {
	$formRow = null;
	if(!empty($_GET['form'])) {
		if(ctype_digit($_GET['form'])) {
			$where = "WHERE id='".$_GET['form']."'";
		} else {
			$where = "WHERE url_segment='".format_userinput($_GET['form'],'alphanum+')."'";
		}
		$formRow = db_select_data('ko_subscription_forms',$where,'*','','',true);
	}

	if(!$formRow) {
		throw new \kOOL\Subscription\FormException('no_form');
	}

	$leuteId = null;
	$groupId = null;
	if($_GET['action'] == 'edit') {
		if(!ko_subscription_validate_key($_GET['key'],$formRow['id'],$leuteId,$groupId,$error,'edit_link')) {
			throw new \kOOL\Subscription\FormException('key_'.$error);
		}

		do {
			$trace = db_select_data('ko_leute_revision_trace','WHERE lapsed_id='.$leuteId,'current_id','','',true);
			if($trace) {
				$leuteId = $trace['current_id'];
			}
		} while($trace);
	}

	if($_GET['action'] == 'doubleoptin' && !empty($_GET['key'])) {
		if(!ko_subscription_validate_key($_GET['key'],$formRow['id'],$doiId,$groupId,$error,'double_opt_in')) {
			throw new \kOOL\Subscription\FormException('key_'.$error);
		}

		$doi = db_select_data('ko_subscription_double_opt_in',"WHERE id='".$doiId."'",'*','','',true);
		if(!$doi) {
			throw new \kOOL\Subscription\FormException('double_opt_in_invalid');
		}

		if($doi['status'] == 0) {
			list($data,$presentationData) = json_decode_latin1($doi['data']);
			$leuteId = ko_subscription_store_subscription($data,$formRow['moderated'],$formRow['overflow']);
			ko_subscription_send_mails($formRow,$presentationData,$data,$leuteId,'subscription');
		}

		db_update_data('ko_subscription_double_opt_in',"WHERE id='".$doiId."'",['status' => 1,'confirmation_time' => date('Y-m-d H:i:s')]);
		ko_log('subscription_double_opt_in_confirm','double-opt-in entry:'.$doiId.' was confirmed by double-opt-in link');

		header('Location: /form/'.$formRow['url_segment'].'/done');
		exit;
	}

	require_once('inc/Form.php');
	$form = new \kOOL\Subscription\Form($formRow);

	if($leuteId) {
		$leuteData = db_select_data('ko_leute',"WHERE id='".$leuteId."'",'*','','',true);
		$form->setEditData($leuteData);
		if($groupId) {
			$form->setEditGroup($groupId);
		}
	}

	if(!$leuteId && ($formRow['protected'] || empty($formRow['groups'])) && $_GET['action'] != 'done') {
		if($formRow['edit_link']) {
			if(empty($_GET['action'])) {
				header('Location: /form/'.$formRow['url_segment'].'/linkform');
				exit;
			}
		} else {
			throw new \kOOL\Subscription\FormException('needs_edit_link');
		}
	}

	if(!$leuteId && $formRow['edit_link'] && empty($_GET['action'])) {
		$form->addActionLink('/form/'.$formRow['url_segment'].'/linkform',getLL('subscription_form_edit_link'));
	}

	if($_GET['action'] == 'linkform') {
		$form->setMode('editLink');
	}

	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($_GET['action'] == 'linkform') {
			ko_subscription_send_edit_link($_POST['email'],$_POST['groupSelect'],$formRow,86400);
			header('Location: /form/'.$formRow['url_segment'].'/linksent');
			exit;
		} else if($data = $form->validate($_POST)) {
			$data['_bemerkung'] = sprintf(getLL('subscription_ko_leute_mod_comment'),$formRow['title']);
			ko_log('subscription_submit',ko_subscription_get_log_message($data));
			$mode = $leuteId ? 'edit' : 'subscription';
			if($mode == 'edit') {
				if(ko_subscription_store_edit($data,$leuteId) !== false) {
					$mode = 'subscription';
				}
				ko_subscription_send_mails($formRow,$form->getPresentationData(),$data,$leuteId,$mode);
				$nextAction = 'done';
			} else {
				if($formRow['double_opt_in']) {
					ko_subscription_send_double_opt_in_mail($formRow,$form->getPresentationData(),$data);
					$nextAction = 'doubleoptin';
				} else {
					$leuteId = ko_subscription_store_subscription($data,$formRow['moderated'],$formRow['overflow']);
					ko_subscription_send_mails($formRow,$form->getPresentationData(),$data,$leuteId,$mode);
					$nextAction = 'done';
				}
			}
			header('Location: /form/'.$formRow['url_segment'].'/'.$nextAction);
			exit;
		}
	}

	switch($_GET['action']) {
		case 'done':
			$title = $formRow['confirmation_title'] ?: getLL('subscription_default_confirmation_title');
			$text = $formRow['confirmation_text'] ?: getLL('subscription_default_confirmation_text');
			ko_subscription_render_form_page('<h1>'.$title.'</h1>'.$text,$formRow['title'], $formRow);
			break;
		case 'linksent':
			ko_subscription_render_form_page(getLL('subscription_form_edit_link_sent'),$formRow['title'], $formRow);
			break;
		case 'doubleoptin':
			$title = $formRow['double_opt_in_title'] ?: getLL('subscription_form_double_opt_in_sent_title');
			$text = $formRow['double_opt_in_text'] ?: getLL('subscription_form_double_opt_in_sent_text');
			ko_subscription_render_form_page('<h1>'.$title.'</h1>'.$text,$formRow['title'], $formRow);
			break;
		default:
			ko_subscription_render_form_page($form,$formRow['title'], $formRow);
	}

} catch(\kOOL\Subscription\FormException $ex) {
	ko_subscription_render_form_page($ex, null, $formRow);
}

function ko_subscription_render_form_page($content,$title = null, &$formRow) {
	global $BASE_PATH, $HTML_TITLE, $PLUGINS, $ko_menu_akt, $ko_path;
?>
<!DOCTYPE html>
<html lang="<?php print $_SESSION["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= "$HTML_TITLE: ".($title ?: getLL("module_".$ko_menu_akt)) ?></title>
<?php
foreach(array(
	'kool-base.css',
	'subscription/res/form.css'
	) as $file) {
	print '<link rel="stylesheet" href="/'.$file.'?'.filemtime($ko_path.$file).'" />'."\n";
}
foreach(array(
	'inc/jquery/jquery.js',
	'inc/jquery/jquery-ui.js',
	'inc/moment/moment.js',
	'inc/bootstrap/core/js/bootstrap.min.js',
	'inc/bootstrap/plugins/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js',
	'subscription/res/form.js'
	) as $file) {
	print '<script src="/'.$file.'?'.filemtime($ko_path.$file).'"></script>'."\n";
}
?>
<?php if($_GET['mode'] == 'iframe') { ?><script src="/subscription/inc/iframeResizer.contentWindow.min.js"></script><?php } ?>
<?php
switch($_SESSION['lang']) {
	case 'de':
		echo '<script src="/inc/moment/de.js"></script>';
		break;
	case 'en':
		echo '<script src="/inc/moment/en-ca.js"></script>';
		break;
	case 'fr':
		echo '<script src="/inc/moment/fr.js"></script>';
		break;
	case 'nl':
		echo '<script src="/inc/moment/en-ca.js"></script>';
}

// include plugin css
foreach($PLUGINS as $plugin) {
	$file = "plugins/".$plugin["name"]."/subscription_forms.css";
	if(file_exists($BASE_PATH.$file)) {
		print '<link rel="stylesheet" href="/'.$file.'?'.filemtime($ko_path.$file).'" />'."\n";
	}
}

// Include CSS for selected layout (if any)
if($formRow['layout']) {
	$file = realpath($BASE_PATH.$formRow['layout']);
	if(file_exists($file) && trim(substr($file, 0, strlen($BASE_PATH.'plugins')) == trim($BASE_PATH.'plugins'))) {
		print '<link rel="stylesheet" href="/'.substr($file, strlen($BASE_PATH)).'?'.filemtime($file).'" />'."\n";
	}
}

?>
</head>
<body class="<?= $_GET['mode'] == 'iframe' ? 'framed' : 'standalone' ?>">

	<div class="page">
		<?php if($_GET['mode'] != 'iframe') { ?>
			<div id="header">
				<div id="default-header">
					<?php include $BASE_PATH.'header.php'; ?>
				</div>
			</div>
		<?php } ?>
		<?php
if(ko_get_setting('subscription_text_header')) {
	print '<div class="subscriptionTextHeader">'.ko_get_setting('subscription_text_header').'</div>';
}

try {
	if($content instanceof kOOL\Subscription\FormException) {
		throw $content;
	}
	if($content instanceof kOOL\Subscription\Form) {
		$content->render();
	} else {
		echo $content;
	}
} catch(\kOOL\Subscription\FormException $ex) {
?>
	<div class="alert alert-danger">
		<strong><?= getLL('subscription_form_error') ?></strong>
		<?= $ex->getMessage() ?>
	</div>
<?php
}

if(ko_get_setting('subscription_text_footer')) {
	print '<div class="subscriptionTextFooter">'.ko_get_setting('subscription_text_footer').'</div>';
}
		?>
	</div>
</body>
<?php
}
