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
$ko_menu_akt = "crm";

include($ko_path . "inc/ko.inc");
include("inc/crm.inc");

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("crm")) {
	header("Location: ".$BASE_URL."index.php"); exit;
}

ob_end_flush();  //Puffer flushen

$onload_code = "";

$notifier = koNotifier::Instance();

//*** Rechte auslesen
ko_get_access('crm');

//kOOL Table Array
ko_include_kota(array('ko_crm_projects', 'ko_crm_status', 'ko_crm_contacts'));

//*** Plugins einlesen:
$hooks = hook_include_main("crm");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


//*** Action auslesen:
if($_POST["action"]) {
	$do_action=$_POST["action"];
	$action_mode = "POST";
} else if($_GET["action"]) {
	$do_action=$_GET["action"];
	$action_mode = "GET";
} else {
	$do_action = $action_mode = "";
}
if(!$do_action) $do_action = "list_contacts";

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {



	// projects
	case 'list_projects':
		if($access['crm']['MAX'] < 1) break;

		$_SESSION['show'] = 'list_crm_projects';
	break;

	case 'new_project':
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'new_crm_project';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_new_project':
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		$newId = kota_submit_multiedit('', 'new_crm_project');

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(1);
			$_SESSION['show'] = 'list_crm_projects';
		}
	break;

	case 'edit_project':
		$editId = format_userinput($_POST['id'], 'uint');
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'edit_crm_project';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_edit_project':
		list($t1, $t2, $editId) = explode('@', $_POST['id']);
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		kota_submit_multiedit('', 'edit_crm_project');
		if(!$notifier->hasErrors()) {
			$notifier->addInfo(2);
			$_SESSION['show'] = 'list_crm_projects';
		}
	break;

	case 'delete_project':
		$id = format_userinput($_POST['id'], 'uint');
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		ko_get_crm_projects($entry, ' and `id` = ' . $id, '', '', TRUE, TRUE);
		if(!$entry['id'] || $entry['id'] != $id) break;

		db_delete_data('ko_crm_projects', "WHERE `id` = '$id'");
		ko_log_diff('del_crm_project', $entry);
		$notifier->addInfo(3);
	break;



	// status
	case 'list_status':
		if($access['crm']['MAX'] < 1) break;

		$_SESSION['show'] = 'list_crm_status';
	break;

	case 'new_status':
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'new_crm_status';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_new_status':
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		$newId = kota_submit_multiedit('', 'new_crm_status');

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(4);
			$_SESSION['show'] = 'list_crm_status';
		}
	break;

	case 'edit_status':
		$editId = format_userinput($_POST['id'], 'uint');
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'edit_crm_status';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_edit_status':
		list($t1, $t2, $editId) = explode('@', $_POST['id']);
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		kota_submit_multiedit('', 'edit_crm_status');
		if(!$notifier->hasErrors()) {
			$notifier->addInfo(5);
			$_SESSION['show'] = 'list_crm_status';
		}
	break;

	case 'delete_status':
		$id = format_userinput($_POST['id'], 'uint');
		if($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) break;

		ko_get_crm_status($entry, ' and `id` = ' . $id, '', '', TRUE, TRUE);
		if(!$entry['id'] || $entry['id'] != $id) break;

		db_delete_data('ko_crm_status', "WHERE `id` = '$id'");
		ko_log_diff('del_crm_status', $entry);
		$notifier->addInfo(6);
	break;



	// contacts
	case 'list_contacts':
		if($access['crm']['MAX'] < 1) break;

		if($_SESSION['crm_show_todos']) {
			$_SESSION['sort_crm_contacts']= 'crdate';
			$_SESSION['sort_crm_contacts_order'] = 'DESC';

			unset($_SESSION['kota_filter']['ko_crm_contacts']['deadline']);
		}

		$_SESSION['crm_show_todos'] = FALSE;
		$_SESSION['show'] = 'list_crm_contacts';
	break;

	case 'new_contact':
		if($access['crm']['MAX'] < 2) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'new_crm_contact';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_new_contact':
		if($access['crm']['MAX'] < 2) break;

		$newId = kota_submit_multiedit('', 'new_crm_contact');

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(7);
			$_SESSION['show'] = 'list_crm_contacts';
		}
	break;

	case 'edit_contact':
		$editId = format_userinput($_POST['id'], 'uint');

		ko_get_crm_contacts($contact, " AND `id` = '" . $editId . "'", '', '', TRUE, TRUE);

		// check access
		if (!ko_get_crm_contacts_access($contact, 'edit')) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'edit_crm_contact';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_edit_contact':
		list($t1, $t2, $editId) = explode('@', $_POST['id']);

		ko_get_crm_contacts($contact, " AND `id` = '" . $editId . "'", '', '', TRUE, TRUE);

		// check access
		if (!ko_get_crm_contacts_access($contact, 'edit')) break;

		kota_submit_multiedit('', 'edit_crm_contact');
		if(!$notifier->hasErrors()) {
			$notifier->addInfo(8);
			$_SESSION['show'] = 'list_crm_contacts';
		}
	break;

	case 'delete_contact':
		$id = format_userinput($_POST['id'], 'uint');

		ko_get_crm_contacts($contact, " AND `id` = '" . $id . "'", '', '', TRUE, TRUE);

		if(!$contact['id'] || $contact['id'] != $id) break;
		// check access
		if (!ko_get_crm_contacts_access($contact, 'delete')) break;

		db_delete_data('ko_crm_contacts', "WHERE `id` = '$id'");
		ko_log_diff('del_crm_contact', $contact);
		$notifier->addInfo(9);
	break;


	case 'list_todos':
		if($access['crm']['MAX'] < 1) break;

		$_SESSION['sort_crm_contacts']= 'deadline';
		$_SESSION['sort_crm_contacts_order'] = 'ASC';

		$_SESSION['kota_filter']['ko_crm_contacts']['deadline'][0] = array('neg' => '', 'from' => '01.01.2000', 'to' => '');

		$_SESSION['crm_show_todos'] = TRUE;
		$_SESSION['show'] = 'list_crm_contacts';
	break;



	case 'crm_settings':
		if($access['crm']['MAX'] < 1) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'crm_settings';
	break;

	case 'submit_crm_settings':
		if($access['crm']['MAX'] < 1) break;

		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_crm_contacts', format_userinput($_POST['txt_limit_contacts'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'default_view_crm', format_userinput($_POST['sel_default_view'], 'alphanum+'));

		//Access check for global settings
		if($access['crm']['MAX'] > 4) {
			ko_set_setting('crm_group_email_project_id', format_userinput($_POST['sel_crm_group_email_project_id'], 'uint'));
			if(ko_module_installed('donations')) {
				ko_set_setting('crm_status_donation', format_userinput($_POST['sel_crm_status_donation'], 'uint'));
			}
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'list_crm_contacts';
	break;



	case 'set_projectfilter':
		if($access['crm']['MAX'] < 1) break;

		//ID from GET
		$id = format_userinput($_GET['id'], 'uint');
		if(!$id) break;

		//Apply filter
		$_SESSION['show_crm_projects'] = array($id);

		$_SESSION['show'] = 'list_crm_contacts';
		$_SESSION['show_start'] = 1;
	break;



	case 'multiedit':
		if($_SESSION["show"] == "list_crm_projects") {
			if ($access['crm']['MAX'] < 5) break;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach ($columns as $column) {
				$do_columns[] = $column;
			}
			if (sizeof($do_columns) < 1) $notifier->addError(1, $do_action);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach ($_POST["chk"] as $c_i => $c) {
				if (!$c) continue;
				if (FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
					trigger_error("Not allowed multiedit_id: " . $c_i, E_USER_ERROR);
				}

				if ($access['crm']['ALL'] > 4 || $access['crm'][$edit_id] > 4) $do_ids[] = $edit_id;
			}
			if (sizeof($do_ids) < 1) $notifier->addError(2, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if (!$notifier->hasErrors()) {
				$order = "ORDER BY " . $_SESSION["sort_crm_projects"] . " " . $_SESSION["sort_crm_projects_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit";
				$multieditTable = 'ko_crm_projects';
			}
		} else if ($_SESSION['show'] == 'list_crm_status') {
			if ($access['crm']['MAX'] < 5) break;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach ($columns as $column) {
				$do_columns[] = $column;
			}
			if (sizeof($do_columns) < 1) $notifier->addError(1, $do_action);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach ($_POST["chk"] as $c_i => $c) {
				if (!$c) continue;
				if (FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
					trigger_error("Not allowed multiedit_id: " . $c_i, E_USER_ERROR);
				}

				$do_ids[] = $edit_id;
			}
			if (sizeof($do_ids) < 1) $notifier->addError(3, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if (!$notifier->hasErrors()) {
				$order = "ORDER BY " . $_SESSION["sort_crm_status"] . " " . $_SESSION["sort_crm_status_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit";
				$multieditTable = 'ko_crm_status';
			}
		} else if ($_SESSION['show'] == 'list_crm_contacts') {
			if ($access['crm']['MAX'] < 2) break;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach ($columns as $column) {
				$do_columns[] = $column;
			}
			if (sizeof($do_columns) < 1) $notifier->addError(1, $do_action);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach ($_POST["chk"] as $c_i => $c) {
				if (!$c) continue;
				if (FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
					trigger_error("Not allowed multiedit_id: " . $c_i, E_USER_ERROR);
				}

				$maybe_do_ids[] = $edit_id;
			}
			if (sizeof($maybe_do_ids) < 1) $notifier->addError(4, $do_action);
			else {
				$contacts = db_select_data('ko_crm_contacts', "WHERE `id` IN (".implode(',', $maybe_do_ids).")");
				$do_ids = array();
				foreach ($contacts as $contact) {
					$projectId = $contact['project_id'];
					$projectAccess = max($access['crm']['ALL'], $access['crm'][$projectId]);
					if ($projectAccess > 3 || ($projectAccess > 1 && $contact['cruser'] == $_SESSION['ses_userid'])) $do_ids[] = $contact['id'];
				}
			}
			if (sizeof($do_ids) < 1) $notifier->addError(4, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if (!$notifier->hasErrors()) {
				$order = "ORDER BY " . $_SESSION["sort_crm_contacts"] . " " . $_SESSION["sort_crm_contacts_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit";
				$multieditTable = 'ko_crm_contacts';
			}
		}
	break;

	case 'submit_multiedit':
		list($table, $cols, $ids, $test) = explode('@', $_POST['id']);

		if ($table == 'ko_crm_projects' || $table == 'ko_crm_status') $minLevel = 5;
		else $minLevel = 2;
		kota_submit_multiedit($minLevel);

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(10, $do_action);
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'list_crm_contacts';
	break;



	//Default:
	default:
		if(!hook_action_handler($do_action)) {
			include($ko_path."inc/abuse.inc");
		}
	break;

}//switch(do_action)


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);


//*** Default-Werte auslesen

// project status filter
$_SESSION['crm_filter']['project_status'] = unserialize(ko_get_userpref($_SESSION['ses_userid'], 'crm_filter_project_status'));
if (!$_SESSION['crm_filter']['project_status']) $_SESSION['crm_filter']['project_status'] = array();

$_SESSION['show_limit'] = ko_get_userpref($_SESSION['ses_userid'], 'show_limit_crm_contacts');
if(!$_SESSION['show_limit']) $_SESSION['show_limit'] = 20;

if(!$_SESSION['show_start']) $_SESSION['show_start'] = 1;

if($_SESSION['sort_crm_projects'] == '') {
	$_SESSION['sort_crm_projects']= 'crdate';
	$_SESSION['sort_crm_projects_order'] = 'DESC';
}

if($_SESSION['sort_crm_status'] == '') {
	$_SESSION['sort_crm_status']= 'crdate';
	$_SESSION['sort_crm_status_order'] = 'DESC';
}

if($_SESSION['sort_crm_contacts'] == '') {
	$_SESSION['sort_crm_contacts']= 'date';
	$_SESSION['sort_crm_contacts_order'] = 'DESC';
}

if(!isset($_SESSION['show_crm_projects']) || $_SESSION['show_crm_projects'] == '') {
	$show_crm_projects_string = ko_get_userpref($_SESSION['ses_userid'], 'show_crm_projects');
	if($show_crm_projects_string) {
		$_SESSION['show_crm_projects'] = explode(',', $show_crm_projects_string);
	} else {
		ko_get_crm_projects($projects);
		$_SESSION['show_crm_projects'] = array_keys($projects);
	}
}


//Include submenus
ko_set_submenues();
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
	include('inc/js-selproject.inc');
	include('inc/js-crm.inc');
	?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");

ko_get_outer_submenu_code('crm');

?>

<main class="main">
	<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
		<input type="hidden" name="action" id="action" value="" />
		<input type="hidden" name="id" id="id" value="" />
		<input type="hidden" name="recurring_amount" id="recurring_amount" value="" />
		<input type="hidden" name="recurring_date" id="recurring_date" value="" />
		<div name="main_content" id="main_content">

			<?php
			foreach ($infos as $info) {
				$notifier->addInfo($info, $do_action, array($info_txt_add));
			}
			if ($notifier->hasNotifications(koNotifier::ALL)) {
				$notifier->notify();
			}

			hook_show_case_pre($_SESSION["show"]);

			switch($_SESSION["show"]) {
				case "list_crm_projects":
					ko_list_crm_projects();
				break;

				case "list_crm_status":
					ko_list_crm_status();
				break;

				case "list_crm_contacts":
					ko_list_crm_contacts();
				break;

				case 'new_crm_project':
					ko_formular_crm_project('new');
				break;

				case 'edit_crm_project':
					ko_formular_crm_project('edit', $editId);
				break;

				case 'new_crm_status':
					ko_formular_crm_status('new');
				break;

				case 'edit_crm_status':
					ko_formular_crm_status('edit', $editId);
				break;

				case 'new_crm_contact':
					ko_formular_crm_contact('new');
				break;

				case 'edit_crm_contact':
					ko_formular_crm_contact('edit', $editId);
				break;

				case "multiedit_crm_projects":
					ko_multiedit_formular("ko_crm_projects", $do_columns, $do_ids, $order, array("cancel" => "list_crm_projects"));
				break;

				case "multiedit_crm_status":
					ko_multiedit_formular("ko_crm_status", $do_columns, $do_ids, $order, array("cancel" => "list_crm_status"));
				break;

				case "multiedit_crm_contacts":
					ko_multiedit_formular("ko_crm_contacts", $do_columns, $do_ids, $order, array("cancel" => "list_crm_contacts"));
				break;

				case 'crm_settings':
					ko_crm_settings();
				break;

				case 'multiedit':
					ko_multiedit_formular($multieditTable, $do_columns, $do_ids);
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
