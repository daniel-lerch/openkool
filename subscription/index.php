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

header('Content-Type: text/html; charset=ISO-8859-1');

ob_start();  //Ausgabe-Pufferung starten

$ko_path = "../";
$ko_menu_akt = "subscription";

include($ko_path . "inc/ko.inc");
include("inc/subscription.inc");

$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

//***Action auslesen:
if($_POST["action"]) $do_action = $_POST["action"];
else if($_GET["action"]) $do_action = $_GET["action"];
else $do_action = "list_forms";

if(!ko_module_installed("subscription")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

ob_end_flush();  //Puffer flushen

//Get access rights
ko_get_access('subscription');

//kOOL Table Array
ko_include_kota(array('ko_subscription_forms', 'ko_subscription_form_groups'));

//*** Plugins einlesen:
$hooks = hook_include_main("subscription");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	case 'list_forms':
		if($access['subscription']['MAX'] < 1) break;

		$_SESSION['show'] = 'list_forms';
	break;

	case 'new_form':
		if($access['subscription']['MAX'] < 1) break;

		$_SESSION['show'] = 'new_form';
	break;

	case 'edit_form':
	case 'delete_form':
		if(($id = format_userinput($_POST['id'], "uint", true)) !== false) {
			$form = db_select_data('ko_subscription_forms','WHERE id='.$id,'*','','',true);
		} else {
			$form = false;
		}

		if(!$form) {
			trigger_error("Not allowed id: ".$_POST['id'], E_USER_ERROR);
		}

		$formAccess = max($access['subscription']['ALL'],$access['subscription'][$form['form_group']]);
		if($formAccess == 1 && $form['cruser'] != $_SESSION['ses_userid']) {
			$formAccess = 0;
		}

		if($formAccess) {
			if($do_action == 'delete_form') {
				db_delete_data('ko_subscription_forms','WHERE id='.$id);
				ko_log_diff('delete_subscription_form',$form);
			}
			$_SESSION['show'] = $do_action == 'edit_form' ? 'edit_form' : 'list_forms';
		}
	break;


	case 'submit_new_form':
		$formGroup = reset($_POST['koi']['ko_subscription_forms']['form_group']);

		if(max($access['subscription']['ALL'],$access['subscription'][$formGroup]) > 0) {
			try {
				kota_submit_multiedit('','new_subscription_form',$changes,true);
			} catch(\kOOL\Subscription\FormKotaException $ex) {
				$notifier->addTextError($ex->getMessage());
			}

			if(!$notifier->hasErrors()) {
				$_SESSION['show'] = 'list_forms';
			}
		}
	break;

	case 'submit_edit_form':
		$splitId = explode('@',$_POST['id']);
		if(($id = format_userinput($splitId[2], "uint", true)) !== false) {
			$form = db_select_data('ko_subscription_forms','WHERE id='.$id,'form_group,cruser','','',true);
		} else {
			$form = false;
		}

		if(!$form) {
			trigger_error("Not allowed id: ".$splitId[2], E_USER_ERROR);
		}

		$formGroup0 = $form['form_group'];
		$formGroup1 = $_POST['koi']['ko_subscription_forms']['form_group'][$id];

		$formAccess0 = max($access['subscription']['ALL'],$access['subscription'][$formGroup0]);
		$formAccess1 = max($access['subscription']['ALL'],$access['subscription'][$formGroup1]);

		if($formAccess0 == 1 && $form['cruser'] != $_SESSION['ses_userid']) {
			$formAccess0 = 0;
		}
		if($formAccess1 == 1 && $form['cruser'] != $_SESSION['ses_userid']) {
			$formAccess1 = 0;
		}

		if($formAccess0 && $formAccess1) {
			try {
				kota_submit_multiedit('','edit_subscription_form');
			} catch(\kOOL\Subscription\FormKotaException $ex) {
				$notifier->addTextError($ex->getMessage());
			}

			if(!$notifier->hasErrors()) {
				$_SESSION['show'] = 'list_forms';
			}
		}
	break;

	case 'list_form_groups':
		if($access['subscription']['MAX'] < 1) break;

		$_SESSION['show'] = 'list_form_groups';
	break;

	case 'new_form_group':
		if($access['subscription']['ALL'] < 2) break;

		$_SESSION['show'] = 'new_form_group';
	break;

	case 'edit_form_group':
		if($access['subscription']['ALL'] < 2) break;

		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE))) {
			trigger_error("Not allowed id: ".$_POST["id"], E_USER_ERROR);
		}

		$_SESSION['show'] = 'edit_form_group';
	break;

	case 'submit_new_form_group':
		if($access['subscription']['ALL'] < 2) break;

		kota_submit_multiedit('', 'new_subscription_form_group');
		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_form_groups';
		}
	break;

	case 'submit_edit_form_group':
		if($access['subscription']['ALL'] < 2) break;

		kota_submit_multiedit('', 'edit_subscription_form_group');
		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_form_groups';
		}
	break;

	case "multiedit":
		if($_SESSION['show'] == 'list_forms') {
			if($access['subscription']['ALL'] < 2) break;

			//Get columns to be edited
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach($columns as $column) {
				$do_columns[] = $column;
			}
			if(sizeof($do_columns) < 1) $notifier->addError(58, $do_action);

			$do_ids = array();
			foreach($_POST["chk"] as $id => $chk) {
				if($chk) {
					if(FALSE === ($id = format_userinput($id, "uint", TRUE))) {
						trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
					}
					$do_ids[] = $id;
				}
			}
			if(sizeof($do_ids) < 1) $notifier->addError(10, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				$order = "ORDER BY ".$_SESSION["sort_forms"]." ".$_SESSION["sort_forms_order"];
				$_SESSION["show_back"] = 'list_forms';
				$_SESSION["show"] = "multiedit_forms";
			}
		}
	break;

	case 'submit_multiedit':
		if($_SESSION['show'] == 'multiedit_forms') {
			if($access['subscription']['MAX'] < 1) break;
			kota_submit_multiedit('','multiedit_subscription_forms');
			if(!$notifier->hasErrors()) $notifier->addInfo(12, $do_action);
		}
		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'list_forms';
	break;

	case 'list_unconfirmed_double_opt_ins':
		if($access['subscription']['MAX'] < 1) break;
		$_SESSION['show'] = 'list_unconfirmed_double_opt_ins';
	break;

	case 'confirm_double_opt_in':
		$doi = db_select_data('ko_subscription_double_opt_in',"WHERE id='".format_userinput($_GET['doi'],'uint')."'",'*','','',true);
		$form = db_select_data('ko_subscription_forms',"WHERE id='".$doi['form']."'",'*','','',true);
		if(!$doi || !$form) {
			trigger_error("Not allowed id: ".$_GET['doi'], E_USER_ERROR);
		}

		if($doi['status'] != 0) break;

		$formAccess = max($access['subscription']['ALL'],$access['subscription'][$form['form_group']]);
		if($formAccess == 0 || ($formAccess == 1 && $form['cruser'] != $_SESSION['ses_userid'])) break;

		list($data,$presentationData) = json_decode_latin1($doi['data']);
		$leuteId = ko_subscription_store_subscription($data,$form['moderated'],$form['overflow']);
		ko_subscription_send_mails($form,$presentationData,$data,$leuteId,'subscription');

		db_update_data('ko_subscription_double_opt_in',"WHERE id='".$doi['id']."'",[
			'confirmation_userid' => $_SESSION['ses_userid'],
			'confirmation_time' => date('Y-m-d H:i:s'),
			'status' => 2,
		]);
		ko_log('subscription_double_opt_in_manual_confirm','user:'.$_SESSION['ses_userid'].' manually confirmed double-opt-in entry:'.$doi['id']);

		$notifier->addTextInfo(getLL('subscription_double_opt_in_confirmed'));
	break;


	//Settings
	case 'subscription_settings':
		if($access['subscription']['MAX'] < 2) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'subscription_settings';
	break;

	case "submit_subscription_settings":
		if($access['subscription']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) break;

		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_forms', format_userinput($_POST['txt_limit_forms'], 'uint'));

		if($access['subscription']['MAX'] > 1) {
			ko_set_setting('subscription_sender_email', format_userinput($_POST['txt_sender_email'], 'email'));
			ko_set_setting('subscription_sender_name', format_userinput($_POST['txt_sender_name'], 'alpha++'));

			ko_set_setting('subscription_text_header', $_POST['txt_header']);
			ko_set_setting('subscription_text_footer', $_POST['txt_footer']);
		}
	break;


	//Default:
	default:
		if(!hook_action_handler($do_action))
		include($ko_path."inc/abuse.inc");
  break;


}//switch(do_action)

//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);


if(!$_SESSION['sort_forms']) $_SESSION['sort_forms'] = 'title';
if(!$_SESSION['sort_forms_order']) $_SESSION['sort_forms_order'] = 'ASC';

$_SESSION['show_limit'] = ko_get_userpref($_SESSION['ses_userid'], 'show_limit_forms');
if(!$_SESSION['show_limit']) $_SESSION['show_limit'] = 20;

if(!$_SESSION['show_start']) $_SESSION['show_start'] = 1;


//Include submenus
ko_set_submenues();

//Smarty-Templates-Engine laden
require("$ko_path/inc/smarty.inc");
?>
<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
<?php
print ko_include_css();

$js_files = array();
$js_files[] = $ko_path.'inc/ckeditor/ckeditor.js';
$js_files[] = $ko_path.'inc/ckeditor/adapters/jquery.js';
print ko_include_js($js_files);

include($ko_path.'inc/js-sessiontimeout.inc');

?>
<script language="javascript" type="text/javascript">
	$(document).ready(function() {
		$('.richtexteditor').ckeditor();
	});
</script>
</head>

<body onload="session_time_init();<?php print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");

ko_get_outer_submenu_code('subscription');

?>

<main class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data" autocomplete="off">  <!-- Hauptformular -->
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<div name="main_content" id="main_content">

<?php
if($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {
	case 'list_forms':
		ko_subscription_form_list();
	break;
	case 'new_form':
		ko_subscription_formular_form('new');
	break;
	case 'edit_form':
		ko_subscription_formular_form('edit',$id);
	break;
	case 'list_form_groups':
		ko_subscription_form_group_list();
	break;
	case 'new_form_group':
		ko_subscription_formular_form_group('new');
	break;
	case 'edit_form_group':
		ko_subscription_formular_form_group('edit',$id);
	break;

	case "multiedit_forms":
		ko_multiedit_formular("ko_subscription_forms", $do_columns, $do_ids, $order, array("cancel" => "list_forms"));
	break;

	case "list_unconfirmed_double_opt_ins":
		ko_subscription_list_unconfirmed_double_opt_ins();
	break;

	case 'subscription_settings':
		ko_subscription_settings();
	break;

	default:
		//HOOK: Plugins erlauben, neue Show-Cases zu definieren
    hook_show_case($_SESSION["show"]);
  break;
}//switch(show)

//HOOK: Plugins erlauben, die bestehenden Show-Cases zu erweitern
hook_show_case_add($_SESSION["show"]);

?>
</div>
</form>
	</main>

	</div>

	<?php include($ko_path . "footer.php"); ?>

</body>
</html>
