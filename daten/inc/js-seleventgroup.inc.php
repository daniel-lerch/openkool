<script language="javascript" type="text/javascript">
<!--



var firstEgSelected = false;

function selEventGroup(gid) {
	//Res group has been selected
	if(gid.slice(0,1) == "i") return false;

	var r = {};
	<?php
	//Check for installed modules
	if(ko_module_installed("rota")) print 'var rota_installed = true;'."\n";
	else print 'var rota_installed = false;'."\n";
	if(ko_module_installed("reservation")) print 'var res_installed = true;'."\n";
	else print 'var res_installed = false;'."\n";

	ko_get_access('reservation');
	$items = db_select_data("ko_resitem", "WHERE 1=1", "*");

	//Build code for event groups with their data
	$code = "";
	$egs = db_select_data("ko_eventgruppen", "WHERE `type` = '0'", "*", "ORDER BY `name` ASC");
	foreach($egs as $eg) {
		if($access['daten']['ALL'] < 2 && $access['daten'][$eg['id']] < 2) continue;
		if($eg["resitems"]) {
			$_resitems = array();
			foreach(explode(",", $eg["resitems"]) as $rid) {
				if($access['reservation']['ALL'] < 2 && $access['reservation'][$rid] < 2) continue;
				$_resitems[] = array('value' => $rid, 'text' => utf8_encode($items[$rid]["name"]));
			}
			$resitems = json_encode($_resitems);
		} else {
			$resitems = "[]";
		}

		$code .= sprintf("var rEl = {};\n", $eg['id']);

		$code .= sprintf("rEl['%s'] = %s;", 'allow_program', (($eg['moderation'] == 0 || $access['daten'][$eg['id']] >= 3) ? "true" : "false"));
		$code .= sprintf("rEl['%s'] = '%s';", 'startzeit', substr($eg["startzeit"], 0, -3));
		$code .= sprintf("rEl['%s'] = '%s';", 'endzeit', substr($eg["endzeit"], 0, -3));
		$code .= sprintf("rEl['%s'] = %s;", 'do_notify', (trim($eg['notify']) ? "'".trim($eg['notify'])."'" : "null"));
		if (ko_module_installed('reservation')) $code .= sprintf("rEl['%s'] = %s;", 'resitems', $resitems);
		if (ko_module_installed('reservation')) $code .= sprintf("rEl['%s'] = '%s';", 'res_startzeit', substr($eg["res_startzeit"], 0, -3));
		if (ko_module_installed('reservation')) $code .= sprintf("rEl['%s'] = '%s';", 'res_endzeit', substr($eg["res_endzeit"], 0, -3));
		if (ko_module_installed('reservation')) $code .= sprintf("rEl['%s'] = %s;", 'responsible_for_res', $eg["responsible_for_res"]);

		if (ko_module_installed('taxonomy')) {
			$terms = ko_taxonomy_get_terms_by_node($eg['id'],'ko_eventgruppen');
			if($terms != null) {
				$term_code = [];
				foreach($terms as $term) {
					$term_code[] = ["id" => $term['id'], "name" => $term['name']];
				}

				$code .= sprintf("rEl['%s'] = %s;", 'terms', json_encode_latin1($term_code));
			}
		}

		foreach ($EVENT_PROPAGATE_FIELDS as $prop) {
			if (!$prop['module'] || ko_module_installed($prop['module'])) {
				$val = $eg[$prop['from']];
				if (!$val && $prop['default']) $val = $prop['default'];
				switch ($prop['type']) {
					case 'date':
					case 'string':
						$code .= sprintf("rEl['%s'] = '%s';", $prop['to'], trim(strtr($val, array("\n" => "\\n", "\r" => "\\r", "\t" => "\\t", "'" => "\'"))));
					break;
					case 'int':
						$code .= sprintf("rEl['%s'] = %d;", $prop['to'], intval($val));
					break;
					case 'bool':
						$code .= sprintf("rEl['%s'] = %s;", $prop['to'], $val ? 'true' : 'false');
					break;
				}
			}
		}

		$code .= sprintf("r['%s'] = rEl;\n", $eg['id']);
	}
	print $code;
	print "daten_label_program_no_access = '" . getLL('daten_label_program_no_access') . "';";
	?>

	var fields = {};

	<?php if ($_SESSION['show'] != 'edit_termin') { ?>




	// Define all the fields
	fields['startzeit'] = $(document.getElementsByName("koi[ko_event][startzeit][0]")[0]);
	fields['endzeit'] = $(document.getElementsByName("koi[ko_event][endzeit][0]")[0]);
	fields['do_notify'] = $(document.getElementsByName('koi[ko_event][do_notify][0]')[0]);
	<?php if (ko_module_installed('reservation')) { ?>fields['res_startzeit'] = $('[name="res_startzeit"]');<?php } ?>
	<?php if (ko_module_installed('reservation')) { ?>fields['res_endzeit'] = $('[name="res_endzeit"]');<?php } ?>
	<?php if (ko_module_installed('reservation')) { ?>fields['resitems'] = $('#sel_ds1_sel_do_res');<?php } ?>
	<?php if (ko_module_installed('reservation')) { ?>fields['responsible_for_res'] = $('[name="sel_responsible_for_res"]');<?php } ?>
	<?php if (ko_module_installed('taxonomy')) { ?>fields['terms'] = $(document.getElementsByName('koi[ko_event][terms][0]')[0]);<?php } ?>
	<?php
	foreach ($EVENT_PROPAGATE_FIELDS as $prop) {
		if (!$prop['module'] || ko_module_installed($prop['module'])) {
			printf("fields['%s'] = $(document.getElementsByName(\"koi[ko_event][%s][0]\")[0]);", $prop['to'], $prop['to']);
		}
	}
	?>

	if(r[gid]) {
		if (firstEgSelected) {
			var c = confirm('<?php print str_replace("'", "\'", getLL('daten_seleventgroup_confirm')) ?>');
			if (!c) return false;
		}

		if(r[gid]['startzeit'] != "00:00" || r[gid]['endzeit'] != "00:00") {
			fields['startzeit'].setVal(r[gid]['startzeit'] == '00:00' ? '' : r[gid]['startzeit'], true);
			fields['endzeit'].setVal(r[gid]['endzeit'] == '00:00' ? '' : r[gid]['endzeit'], true);
		}
		if(r[gid]['do_notify'] !== null) {
			fields['do_notify'].closest('.formular-cell').show();
			fields['do_notify'].setVal(1, true);
		} else {
			fields['do_notify'].setVal(1, true);
			fields['do_notify'].closest('.formular-cell').hide();
		}


		// Set 'data-preset-join-value-local' of foreign table load presets button to eventgroup id
		$('button.form_ft_load_preset[data-field="ko_event.program"]').attr('data-preset-join-value-local', gid);
		$(".ko_event_warning_program").remove();
		if (!r[gid]['allow_program']) {
			$('span.form_ft_new[data-field="ko_event.program"]')
				.parent('td.formular_content')
				.hide()
				.parent('tr')
				.append('<span style="font-size:10px;margin:8px;color:#FC6231;" class="ko_event_warning_program">' + daten_label_program_no_access + '</span>');
		}
		else {
			$(".ko_event_warning_program").remove();
			$('span.form_ft_new[data-field="ko_event.program"]').parent('td.formular_content').show();
		}


		<?php if (ko_module_installed('reservation')) { ?>

		if(r[gid]['res_startzeit'] != "00:00" || r[gid]['res_endzeit'] != "00:00") {
			fields['res_startzeit'].setVal(r[gid]['res_startzeit'] == '00:00' ? '' : r[gid]['res_startzeit'], true);
			fields['res_endzeit'].setVal(r[gid]['res_endzeit'] == '00:00' ? '' : r[gid]['res_endzeit'], true);
		}
		fields['resitems'].setVal(r[gid]['resitems'], true);
		if(r[gid]['responsible_for_res']) fields['responsible_for_res'].setVal(r[gid]['responsible_for_res'], true);
		else fields['responsible_for_res'].setVal(<?= $_SESSION['ses_userid']; ?>, true);

		<?php }

			if (ko_module_installed('taxonomy')) {
		?>

		var ids = [];
		var selectedDataFromDynamicSearch = [];

		fields['terms'].parent().find(".dynamicsearch-buttons-wrapper").text("");
		if(r[gid]['terms'] !== undefined) {
			r[gid]['terms'].forEach(function (element, index) {
				var terms_code = "<button type=\"button\" class=\"dynamicsearch-button taxonomy-term__button btn btn-sm btn-primary\" " +
					"title=\"" + element['name'] + " entfernen\" data-id=\"" + element['id'] + " \"><span class=\"pull-left\">" + element['name'] + "</span>" +
					"<i class=\"text-danger pull-right fa fa-remove icon-line-height\"></i></button>";
				selectedDataFromDynamicSearch.push({'id': element.id, 'name': element.name, 'title': element.name, 'placeholder': ""});

				fields['terms'].parent().find(".dynamicsearch-buttons-wrapper").append(terms_code);
				ids.push(element['id']);
			});

			fields['terms'].setVal(ids.join(","));
			$('#koi_ko_event_terms_0').data('dynamicsearch').selectedData = selectedDataFromDynamicSearch;
		}


		<?PHP
			}

		foreach ($EVENT_PROPAGATE_FIELDS as $prop) {
			if (!$prop['module'] || ko_module_installed($prop['module'])) {
				printf("\t\tif(r[gid]['".$prop['to']."']) fields['%s'].setVal(r[gid]['%s'], true);\n", $prop['to'], $prop['to']);
			}
		}

		?>

		firstEgSelected = true;
	}//if(r[gid])

	<?php } else { ?>

	fields['do_notify'] = $(document.getElementsByName('koi[ko_event][do_notify][<?= $edit_id ?>]')[0]);

	if(r[gid]) {
		if(r[gid]['do_notify'] !== null) {
			fields['do_notify'].closest('.formular-cell').show();
			fields['do_notify'].setVal(1, true);
		} else {
			fields['do_notify'].setVal(1, true);
			fields['do_notify'].closest('.formular-cell').hide();
		}
	}

	<?php } ?>

	return true;
}//selEventGruppe()

-->
</script>
