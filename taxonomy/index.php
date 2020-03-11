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
$ko_menu_akt = "taxonomy";

include_once($ko_path . "inc/ko.inc");
include_once("inc/taxonomy.inc");

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("taxonomy") || $_SESSION['ses_userid'] == ko_get_checkin_user_id()) {
	header('Location: '.$BASE_URL.'index.php'); exit;
}

ob_end_flush();  //Puffer flushen

$notifier = koNotifier::Instance();

//Get access rights
ko_get_access('taxonomy');

//kOOL Table Array
ko_include_kota(array('ko_taxonomy_terms', 'ko_taxonomy_index'));

//*** Plugins einlesen:
$hooks = hook_include_main("taxonomy");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

//***Action auslesen:
if($_POST["action"]) $do_action = $_POST["action"];
else if($_GET["action"]) $do_action = $_GET["action"];
else $do_action = "";

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {
	case "list_terms":
		$_SESSION['show'] = 'list_terms';
		break;

	case "new_term":
		if ($access['taxonomy']['MAX'] < 2) break;

		$_SESSION["show"] = "new_term";
		$onload_code = "form_set_first_input();" . $onload_code;
		break;

	case "delete_term":
		if ($access['taxonomy']['MAX'] < 2) break;

		$term_id = format_userinput($_POST["id"], "uint");
		if(!$term_id) break;

		$term = ko_taxonomy_get_term_by_id($term_id);
		if(ko_taxonomy_delete_term($term_id) == TRUE) {
			$notifier->addTextInfo(getLL('taxonomy_term_successfully_deleted'));
			ko_log("delete_term", "id: $term_id, ".$term['name']);
		} else {
			$notifier->addTextError(getLL('taxonomy_term_not_deleted'));
		}
		break;

	case "edit_term":
		$term_id = format_userinput($_POST['id'], 'uint');
		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"] = "edit_term";
		break;

	case 'submit_edit_term':
	case 'submit_new_term':
		if ($access['taxonomy']['MAX'] < 2) break;

		kota_submit_multiedit('', 'new_term', $changes);
		if (!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_terms';
			$notifier->addTextInfo(getLL("taxonomy_term_saved"), $do_action);
		}
		break;

	case 'taxonomy_settings':
		$_SESSION["show"] = "taxonomy_settings";
		break;

	case 'submit_taxonomy_settings':
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_taxonomy', format_userinput($_POST['txt_limit_taxonomy'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'default_view_taxonomy', format_userinput($_POST['sel_default_view'], 'js'));
		$_SESSION['show'] = 'list_terms';
		break;

}


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);

// If we are handling a request that was redirected by /inc/form.php, then exit here
if ($asyncFormSubmit == 1) {
	throw new Exception('async-form-submit-dummy-exception');
}

$_SESSION["show_limit"] = ko_get_userpref($_SESSION["ses_userid"], "show_limit_taxonomy");
if(!$_SESSION["show_limit"]) $_SESSION["show_limit"] = ko_get_setting("show_limit_taxonomy");

//Include submenus
ko_set_submenues();
?>
<!DOCTYPE html
		PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
	<?php
	print ko_include_css();

	$js_files = array();
	print ko_include_js($js_files);

	include($ko_path.'inc/js-sessiontimeout.inc');
	include('inc/js-taxonomy.inc');

	?>
</head>

<body onload="session_time_init();<?php print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");

ko_get_outer_submenu_code('taxonomy');
?>


<!-- Hauptbereich -->
<main class="main">
	<form action="index.php" method="post" name="formular" enctype="multipart/form-data">  <!-- Hauptformular -->
		<input type="hidden" name="action" id="action" value="" />
		<input type="hidden" name="id" id="id" value="" />
		<div name="main_content" id="main_content">

		<?php
			if($notifier->hasNotifications(koNotifier::ALL)) {
				$notifier->notify();
			}

			hook_show_case_pre($_SESSION["show"]);

			switch($_SESSION["show"]) {

				case "list_terms":
					ko_taxonomy_list(TRUE, $highlight_group);
				break;

				case "new_term":
					ko_taxonomy_formular_term("new");
				break;

				case "edit_term":
					ko_taxonomy_formular_term("edit", format_userinput($_POST['id'], 'uint'));
				break;

				case "taxonomy_settings":
					ko_taxonomy_settings();
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
