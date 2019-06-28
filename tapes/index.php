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

ob_start();  //Ausgabe-Pufferung starten

$ko_path = "../";
$ko_menu_akt = "tapes";

include($ko_path . "inc/ko.inc");
include("inc/tapes.inc");

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("tapes")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

ob_end_flush();  //Puffer flushen

$notifier = koNotifier::Instance();

//Get access rights
ko_get_access('tapes');

//*** kOOL Table Array
ko_include_kota(array('ko_tapes', 'ko_tapes_groups', 'ko_tapes_series'));

//*** Plugins einlesen:
$hooks = hook_include_main("tapes");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


//*** Action auslesen:
if($_POST["action"]) $do_action = $_POST["action"];
else if($_GET["action"]) $do_action = $_GET["action"];
else $do_action = "";

switch($do_action) {

	/**
	  * Neu
		*/
	case "new_tape":
		if($access['tapes']['MAX'] < 3) continue;
		$_SESSION["show"] = "new_tape";
		$onload_code = "form_set_first_input();".$onload_code;
	break;

	
	case "submit_new_tape":
		if($access['tapes']['MAX'] < 3) continue;

		kota_submit_multiedit("", "new_tape");
		if(!$notifier->hasErrors()) $_SESSION["show"] = "list_tapes";
	break;


	case "submit_edit_tape":
		if($access['tapes']['MAX'] < 3) continue;

		kota_submit_multiedit(3, 'edit_tape');
		if(!$notifier->hasErrors()) $_SESSION["show"] = "list_tapes";
	break;


	case "new_serie":
		if($access['tapes']['MAX'] < 3) continue;

		$_SESSION["show"] = "new_serie";
		$onload_code = "form_set_first_input();".$onload_code;
	break;


	case "submit_new_serie":
		if($access['tapes']['MAX'] < 3) continue;

		kota_submit_multiedit("", "edit_serie");
		if(!$notifier->hasErrors()) $_SESSION["show"] = "list_series";
	break;


	case "submit_edit_serie":
		if($access['tapes']['MAX'] < 3) continue;

		kota_submit_multiedit(0, 'edit_serie');
		if(!$notifier->hasErrors()) $_SESSION["show"] = "list_series";
	break;


	case "new_tapegroup":
		if($access['tapes']['MAX'] < 4) continue;

		$_SESSION["show"] = "new_tapegroup";
		$onload_code = "form_set_first_input();".$onload_code;
	break;


	case "submit_new_tapegroup":
		if($access['tapes']['MAX'] < 4) continue;

		kota_submit_multiedit("", "new_tapegroup");
		if(!$notifier->hasErrors()) $_SESSION["show"] = "list_tapegroups";
	break;



	case "submit_edit_tapegroup":
		if($access['tapes']['MAX'] < 4) continue;

		kota_submit_multiedit(4, 'new_tapegroup');
		if(!$notifier->hasErrors()) $_SESSION["show"] = "list_tapegroups";
	break;



	case "new_printlayout":
		if($access['tapes']['MAX'] < 4) continue;

		$_SESSION["show"] = "new_printlayout";
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "submit_edit_printlayout":
	case "submit_new_printlayout":
		if($access['tapes']['MAX'] < 4) continue;

		//ID bei Edit
		if($do_action == "submit_edit_printlayout") {
			$id = format_userinput($_POST["id"], "uint");
			if(!$id) {
				$notifier->addError(6, $do_action);
				continue;
			}
		}

		//Name
		$save_name = format_userinput($_POST["txt_name"], "js");
		if($save_name == "") {
			$notifier->addError(4, $do_action);
			continue;
		}

		//Formular-Daten in Array speichern
		foreach($_POST["frm"] as $key => $value) {
			if($value["do"]) {  //Nicht verwenden
				$layout[$key] = array("do" => 0);
			} else {
				$layout[$key] = array("do" => 1,
						"x" => $value["x"],
						"y" => $value["y"],
						"font" => $value["font"],
						"fontsize" => $value["fontsize"],
						"align" => $value["align"]
				);
			}
		}
		$new_layout = array(
				"page_width" => format_userinput($_POST["txt_page_width"], "uint", FALSE, 10),
				"page_height" => format_userinput($_POST["txt_page_height"], "uint", FALSE, 10),
				"items" => format_userinput($_POST["txt_items"], "uint", FALSE, 3),
				"rootx" => explode(",", format_userinput($_POST["txt_rootx"], "intlist")),
				"rooty" => explode(",", format_userinput($_POST["txt_rooty"], "intlist")),
				"layout" => $layout
		);

		if($do_action == "submit_new_printlayout") {
			//Array als serialized neu speichern
			db_insert_data('ko_tapes_printlayout', array('name' => $save_name, 'data' => serialize($new_layout)));
			//Log-Meldung
			ko_log("new_tape_printlayout", $save_name);
			$notifier->addInfo(7, $do_action);
		} else {
			//Array als serialized wieder speichern
			db_update_data('ko_tapes_printlayout', "WHERE `id` = '$id'", array('name' => $save_name, 'data' => serialize($new_layout)));
			//Log-Meldung
			ko_log("edit_tape_printlayout", $save_name);
			$notifier->addInfo(8, $do_action);
		}

		$_SESSION["show"] = "list_printlayouts";
	break;






	/**
	  * Anzeige
		*/
	case "list_tapes":
		if($access['tapes']['MAX'] < 1) continue;
		$_SESSION["show"] = "list_tapes";
		$_SESSION["show_start"] = 1;
	break;


	case "list_series":
		if($access['tapes']['MAX'] < 1) continue;
		$_SESSION["show"] = "list_series";
	break;


	case "list_tapegroups":
		if($access['tapes']['MAX'] < 4) continue;
		$_SESSION["show"] = "list_tapegroups";
	break;


	case "list_printlayouts":
		if($access['tapes']['MAX'] < 4) continue;
		$_SESSION["show"] = "list_printlayouts";
	break;





	/**
	  * Einstellungen"
		*/
	case "settings":
		if($access['tapes']['MAX'] < 4) continue;

		$_SESSION["show"] = "settings";
	break;

	
	case "save_settings":
		if($access['tapes']['MAX'] < 4) continue;

		//Zahl- und Ja/Nein-Werte
		$new_plus = format_userinput($_POST["txt_new_plus"], "uint");
		$new_minus = format_userinput($_POST["txt_new_minus"], "uint");
		if($new_plus < 0 || $new_plus > 100 || $new_minus < 0 || $new_minus > 100) $notifier->addError(5, $do_action);
		$guess_series = format_userinput($_POST["rd_guess_series"], "uint", FALSE, 1);
		$clear_printqueue = format_userinput($_POST["rd_clear_printqueue"], "uint", FALSE, 1);
		
		if(!$notifier->hasErrors()) {
			ko_set_setting("tapes_new_plus", $new_plus);
			ko_set_setting("tapes_new_minus", $new_minus);
			ko_set_setting("tapes_guess_series", $guess_series);
			ko_set_setting("tapes_clear_printqueue", $clear_printqueue);
		}

		//Default Printlayout
		$default_layout = format_userinput($_POST["sel_default_printlayout"], "uint");
		db_update_data('ko_tapes_printlayout', "WHERE `default` = '1'", array('default' => '0'));
		db_update_data('ko_tapes_printlayout', "WHERE `id` = '$default_layout'", array('default' => '1'));

		$notifier->addInfo(5, $do_action);
		ko_log("tapes_settings", $new_minus."-".$new_plus.", Clear Queue: ".$clear_printqueue.", Guess Series: ".$guess_series);
	break;






	/**
	  * Bearbeiten
		*/
	case "edit_tape":
		$id = format_userinput($_POST["id"], "uint");
		ko_get_tapes($tape, "AND ko_tapes.id = '$id'");
		if($id && ($access['tapes']['ALL'] > 2 || $access['tapes'][$tape[$id]['group_id']] > 2)) {
			$edit_id = $id;
			$_SESSION["show"] = "edit_tape";
			$onload_code = "form_set_first_input();".$onload_code;
		}
	break;


	case "edit_tapegroup":
		$id = format_userinput($_POST["id"], "uint");
		if($id && ($access['tapes']['ALL'] > 3 || $access['tapes'][$id] > 3)) {
			$edit_id = $id;
			$_SESSION["show"] = "edit_tapegroup";
			$onload_code = "form_set_first_input();".$onload_code;
		}
	break;


	case "edit_serie":
		$id = format_userinput($_POST["id"], "uint");
		if($id && ($access['tapes']['MAX'] > 2)) {
			$edit_id = $id;
			$_SESSION["show"] = "edit_serie";
			$onload_code = "form_set_first_input();".$onload_code;
		}
	break;


	case "edit_printlayout":
		$id = format_userinput($_POST["id"], "uint");
		if($id && ($access['tapes']['MAX'] > 3)) {
			$edit_id = $id;
			$_SESSION["show"] = "edit_printlayout";
			$onload_code = "form_set_first_input();".$onload_code;
		}
	break;





	case "delete_tape":
		$id = format_userinput($_POST["id"], "uint");
		ko_get_tapes($del_tape, "AND ko_tapes.id = '$id'");
		if($id && ($access['tapes']['ALL'] > 2 || $access['tapes'][$del_tape[$id]['group_id']] > 2)) {
			db_delete_data('ko_tapes', "WHERE `id` = '$id'");

			$log_message = implode(", ", $del_tape[$id]);
			ko_log("delete_tape", $log_message);
			$notifier->addInfo(3, $do_action);
		}
	break;


	case "delete_tapegroup":
		$id = format_userinput($_POST["id"], "uint");
		ko_get_tapegroups($tapegroup, "AND ko_tapes_groups.id = '$id'");
		if($id && ($access['tapes']['ALL'] > 3 || $access['tapes'][$id] > 3)) {
			db_delete_data('ko_tapes_groups', "WHERE `id` = '$id'");

			$log_message = implode(", ", $tapegroup[$id]);
			ko_log("delete_tapegroup", $log_message);
			$notifier->addInfo(3, $do_action);
		}
	break;


	case "delete_serie":
		$id = format_userinput($_POST["id"], "uint");
		ko_get_tapeseries($serie, "AND ko_tapes_series.id = '$id'");
		if($id && ($access['tapes']['MAX'] > 2)) {
			db_delete_data('ko_tapes_series', "WHERE `id` = '$id'");

			$log_message = implode(", ", $serie[$id]);
			ko_log("delete_tapeserie", $log_message);
			$notifier->addInfo(3, $do_action);
		}
	break;


	case "delete_printlayout":
		$id = format_userinput($_POST["id"], "uint");
		$layout = ko_get_tape_printlayout($id);
		if($id && ($access['tapes']['MAX'] > 3)) {
			db_delete_data('ko_tapes_printlayout', "WHERE `id` = '$id'");

			$log_message = implode(", ", $layout);
			ko_log("delete_tape_printlayout", $log_message);
			$notifier->addInfo(3, $do_action);
		}
	break;







	/**
	  * Filter
		*/
	case "submit_group_filter":
		$id = format_userinput($_GET["set_filter"], "uint");
		if(!$id) unset($_SESSION["tape_group_filter"]);
		else {
			ko_get_tapegroups($groups);
			$found = FALSE;
			foreach($groups as $i => $group) {
				if($i == $id) $found = TRUE;
			}
			if($found) $_SESSION["tape_group_filter"] = $id;
			else unset($_SESSION["tape_group_filter"]);
		}
		$_SESSION["show"] = "list_tapes";
		$_SESSION["show_start"] = 1;
	break;

	case "submit_serie_filter":
		$id = format_userinput($_GET["set_filter"], "uint");
		if(!$id) unset($_SESSION["tape_serie_filter"]);
		else {
			ko_get_tapeseries($series);
			$found = FALSE;
			foreach($series as $i => $serie) {
				if($i == $id) $found = TRUE;
			}
			if($found) $_SESSION["tape_serie_filter"] = $id;
			else unset($_SESSION["tape_serie_filter"]);
		}
		$_SESSION["show"] = "list_tapes";
		$_SESSION["show_start"] = 1;
	break;

	case "submit_title_filter":
		$title = format_userinput($_POST["id"], "text");
		if(!$title) unset($_SESSION["tape_title_filter"]);
		else {
			$_SESSION["tape_title_filter"] = $title;
		}
		$_SESSION["show_start"] = 1;
	break;

	case "submit_subtitle_filter":
		$title = format_userinput($_POST["id"], "text");
		if(!$title) unset($_SESSION["tape_subtitle_filter"]);
		else {
			$_SESSION["tape_subtitle_filter"] = $title;
		}
		$_SESSION["show_start"] = 1;
	break;
			
	case "submit_preacher_filter":
		$preacher = format_userinput(urldecode($_GET["set_filter"]), "text");
		if(!$preacher) unset($_SESSION["tape_preacher_filter"]);
		else {
			$_SESSION["tape_preacher_filter"] = $preacher;
		}
		$_SESSION["show_start"] = 1;
	break;


	case "clear_filters":
		unset($_SESSION["tape_group_filter"]);
		unset($_SESSION["tape_serie_filter"]);
		unset($_SESSION["tape_title_filter"]);
		unset($_SESSION["tape_subtitle_filter"]);
		unset($_SESSION["tape_preacher_filter"]);
		$_SESSION["show_start"] = 1;
	break;




	/**
	  * Drucken
		*/
	case "add_to_printqueue":
		foreach($_POST["txt"] as $c_i => $c) {
			$i = format_userinput($c, "uint", FALSE, 2);
    	if($i < 100 && $i > 0) {
				if($_SESSION["printqueue"][$c_i] > 0) {
					$_SESSION["printqueue"][$c_i] += $i;
				} else {
					$_SESSION["printqueue"][$c_i] = $i;
				}
			}
		}//foreach(_POST[txt])
		$notifier->addInfo(6, $do_action);
		$_SESSION["show"] = "list_tapes";
	break;


	case "del_from_printqueue":
		if($access['tapes']['MAX'] < 2) continue;
		$id = format_userinput($_POST["sel_printqueue"], "uint");
		if($id > 0) {
			unset($_SESSION["printqueue"][$id]);
		}
	break;


	case "clear_printqueue":
		if($access['tapes']['MAX'] < 2) continue;
		unset($_SESSION["printqueue"]);
	break;


	case "do_print":
		$queue = array();
		foreach($_SESSION["printqueue"] as $id => $num) {
			for($i=0; $i<$num; $i++) {
				$queue[] = $id;
			}
		}

		$printed = 0;
		$continue = TRUE;

		$num_to_print = sizeof($_SESSION["printqueue"]);
		$layout_id = format_userinput($_POST["sel_printlayout"], "uint");
		$layout = ko_get_tape_printlayout($layout_id);


		//PDF-Konstanten
    $PDF_sizex = $layout["page_width"] ? $layout["page_width"] : 210;
    $PDF_sizey = $layout["page_height"] ? $layout["page_height"] : 297;

    //PDF-Datei starten
    define('FPDF_FONTPATH',$ko_path.'fpdf/schriften/');
    require($ko_path.'fpdf/fpdf.php');
    $pdf = new FPDF('P', 'mm', array($PDF_sizex, $PDF_sizey));  //Breite und Höhe in Millimeter
		$pdf->SetMargins(0, 0);
    $pdf->Open();

		//Find actually used fonts first
		$used_fonts = array();
		foreach(array("title", "subtitle", "date", "group", "serie", "preacher", "item_number", "price") as $i) {
			$used_fonts[] = $layout["layout"][$i]["font"];
		}
		$used_fonts = array_unique($used_fonts);
		$fonts = ko_get_pdf_fonts();
		foreach($fonts as $font) {
			if(!in_array($font["id"], $used_fonts)) continue;
			$pdf->AddFont($font["id"], '', $font["file"]);
		}


		$item = format_userinput($_POST["sel_printstart"], "uint");
		while($item >= $layout["items"]) {  //Überlauf abfangen
			$item -= $layout["items"];
		}
	
		while($continue) {
			unset($data);
			for($i=$item; $i<$layout["items"]; $i++) {
				$data[$i]["id"] = array_shift($queue);
			}
			ko_tape_print($pdf, $layout, $data);
			$item = 0;
			if(!is_array($queue) || sizeof($queue) == 0) {
				$continue = FALSE;
			}
		}//while(continue)
		$dateiname = getLL("tapes_filename").strftime("%d%m%Y_%H%M%S", time()) . ".pdf";
		$pdf->Output($ko_path."download/pdf/".$dateiname, false);
		$onload_code = "ko_popup('".$ko_path."download.php?action=passthrough&amp;file=".$dateiname."');";

		$log_tapes = ""; foreach($_SESSION["printqueue"] as $id => $num) $log_tapes .= $num."x".$id.", ";
		$log_message = "preset: ".$layout_id.", start: ".format_userinput($_POST["sel_printstart"], "uint").", tapes: ".substr($log_tapes,0,-2);
		ko_log("tapes_print", $log_message);
		
		//Queue löschen, falls gewünscht
		if(ko_get_setting("tapes_clear_printqueue") == 1) $_SESSION["printqueue"] = array();
	break;





	//Submenus
  case "move_sm_left":
  case "move_sm_right":
    ko_submenu_actions("tapes", $do_action);
  break;


	//Default:
  default:
	if(!hook_action_handler($do_action))
    include($ko_path."inc/abuse.inc");
  break;


}//switch(do_action)

//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);


//***Rechte neu auslesen:
if(in_array($do_action, array('delete_tapegroup', 'submit_new_tapegroup'))) {
	ko_get_access('tapes', '', TRUE);
}



//Filter (rechts)

//***Defaults einlesen
if(!$_SESSION["sort_tapes"]) $_SESSION["sort_tapes"] = "date";
if(!$_SESSION["sort_tapes_order"]) $_SESSION["sort_tapes_order"] = "DESC";
if(!$_SESSION["show_start"]) $_SESSION["show_start"] = 1;
$_SESSION["show_limit"] = ko_get_userpref($_SESSION["ses_userid"], "show_limit_tapes");
if(!$_SESSION["show_limit"]) $_SESSION["show_limit"] = ko_get_setting("show_limit_tapes");

//Smarty-Templates-Engine laden
require("$ko_path/inc/smarty.inc");

//Include submenus
ko_set_submenues();
?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
<?php
print ko_include_css();

print ko_include_js(array($ko_path.'inc/jquery/jquery.js', $ko_path.'inc/kOOL.js'));

include($ko_path.'inc/js-sessiontimeout.inc');
include("inc/js-tapes.inc");
$js_calendar->load_files();
?>
</head>

<body onload="session_time_init();<?php print $onload_code; ?>" oncontextmenu="return false;">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");
?>


<table width="100%">
<tr> 

<!-- Submenu -->
<td class="main_left" name="main_left" id="main_left">
<?php
print ko_get_submenu_code("tapes", "left");
?>
&nbsp;
</td>


<!-- Hauptbereich -->
<td class="main">
<form action="index.php" method="post" name="formular">
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<div name="main_content" id="main_content">

<?php
if($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {

	case "list_tapes":
		ko_tapes_list();
	break;

	case "list_tapegroups":
		ko_tapes_list_tapegroups();
	break;

	case "list_series":
		ko_tapes_list_series();
	break;
	
	case "list_printlayouts":
		ko_tapes_list_printlayouts();
	break;

	case "new_tape":
		ko_tapes_formular_tape("neu");
	break;

	case "new_serie":
		ko_tapes_formular_serie("neu");
	break;

	case "new_tapegroup":
		ko_tapes_formular_tapegroup("neu");
	break;

	case "new_printlayout":
		ko_tapes_formular_printlayout("neu");
	break;

	case "edit_tape":
		ko_tapes_formular_tape("edit", $edit_id);
	break;

	case "edit_tapegroup":
		ko_tapes_formular_tapegroup("edit", $edit_id);
	break;

	case "edit_serie":
		ko_tapes_formular_serie("edit", $edit_id);
	break;

	case "edit_printlayout":
		ko_tapes_formular_printlayout("edit", $edit_id);
	break;

	case "settings":
		ko_tapes_settings();
	break;


	default:
		//HOOK: Plugins erlauben, neue Show-Cases zu definieren
    hook_show_case($_SESSION["show"]);
  break;
}//switch(show)

//HOOK: Plugins erlauben, die bestehenden Show-Cases zu erweitern
hook_show_case_add($_SESSION["show"]);

?>
&nbsp;
</div>
</form>
</td>

<td class="main_right" name="main_right" id="main_right">

<?php
print ko_get_submenu_code("tapes", "right");
?>
&nbsp;
</td>
</tr>

<?php include($ko_path . "footer.php"); ?>

</table>

</body>
</html>
