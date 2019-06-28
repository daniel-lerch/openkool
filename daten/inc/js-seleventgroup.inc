<script language="javascript" type="text/javascript">
<!--
function EG(t1, t2, rota, room, resitems, rest1, rest2, title, kommentar, allow_program) {
	this.t1 = t1;
	this.t2 = t2;
	this.rota = rota;
	this.room = room;
	this.resitems = resitems;
	this.rest1 = rest1;
	this.rest2 = rest2;
	this.title = title.trim();
	this.kommentar = kommentar.trim();
	this.allow_program = allow_program;
}

function selEventGroup(gid) {
	//Res group has been selected
	if(gid.slice(0,1) == "i") return false;

	var r = new Object();
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
				$_resitems[] = $items[$rid]["name"]."@@".$rid;
			}
			$resitems = $eg["resitems"]."@@@@".implode("@@@", $_resitems);
		} else {
			$resitems = "";
		}
		$code .= sprintf("r['%s'] = new EG('%s', '%s', %s, '%s', '%s', '%s', '%s', '%s', '%s', %s);\n",
										 $eg["id"],                            //id
										 substr($eg["startzeit"], 0, -3),      //Startzeit
										 substr($eg["endzeit"], 0, -3),        //Endzeit
										 ($eg["rota"] ? "true" : "false"),     //Rota
										 $eg["room"],                          //Room
										 $resitems,                            //Resitems
										 substr($eg["res_startzeit"], 0, -3),  //Res Startzeit
										 substr($eg["res_endzeit"], 0, -3),    //Res Endzeit
										 strtr($eg["title"], array("'" => "\'")),  //Title
										 strtr($eg["kommentar"], array("\n" => "|", "\r" => "", "\t" => "", "'" => "\'")),  //Comments
										 (($eg['moderation'] == 0 || $access['daten'][$eg['id']] >= 3) ? "true" : "false") // allow adding program entries
										 );
	}
	print $code;
	print "daten_label_program_no_access = '" . getLL('daten_label_program_no_access') . "';";
	?>

	koi_room = document.getElementsByName("koi[ko_event][room][0]")[0];
	koi_title = document.getElementsByName("koi[ko_event][title][0]")[0];
	koi_kommentar = document.getElementsByName("koi[ko_event][kommentar][0]")[0];
	koi_kommentar_rte = document.getElementById("cke_koi[ko_event][kommentar][0]");
	if(rota_installed) koi_rota = document.getElementsByName("koi[ko_event][rota][0]")[0];
	koi_zeit = document.getElementsByName("koi[ko_event][startzeit][0]")[0];
	koi_zeit2 = document.getElementsByName("koi[ko_event][endzeit][0]")[0];

	if(r[gid]) {
		if(r[gid]["t1"] != "00:00") koi_zeit.value = r[gid]["t1"];
		if(r[gid]["t2"] != "00:00") koi_zeit2.value = r[gid]["t2"];
		koi_room.value = r[gid]["room"];
		koi_title.value = r[gid]["title"].replace(/\|/g, "\n");

		// Set 'data-preset-join-value-local' of foreign table load presets button to eventgroup id
		$('button.form_ft_load_preset[data-field="ko_event.program"]').attr('data-preset-join-value-local', gid);

		$(".ko_event_warning_program").remove();
		if (!r[gid]["allow_program"]) {
			$('span.form_ft_new[data-field="ko_event.program"]').parent('td.formular_content').hide();
			$('span.form_ft_new[data-field="ko_event.program"]').parent('td.formular_content').parent('tr').append('<span style="font-size:10px;margin:8px;color:#FC6231;" class="ko_event_warning_program">' + daten_label_program_no_access + '</span>');
		}
		else {
			$(".ko_event_warning_program").remove();
			$('span.form_ft_new[data-field="ko_event.program"]').parent('td.formular_content').show();
		}

		// check whether the description is in a ckeditor-box or in a textarea
		if (koi_kommentar_rte == null) {
			koi_kommentar.value = r[gid]["kommentar"].replace(/\|/g, "\n");
		}
		else {
			for(var i in CKEDITOR.instances) {
				if (CKEDITOR.instances[i].name == "koi[ko_event][kommentar][0]") {
					CKEDITOR.instances[i].setData(r[gid]["kommentar"]);
				}
			}
		}

		if(rota_installed) koi_rota.checked = r[gid]["rota"];

		if(res_installed) {
			if(r[gid]["rest1"] != "") document.formular.res_startzeit.value = r[gid]["rest1"];
			if(r[gid]["rest2"] != "") document.formular.res_endzeit.value = r[gid]["rest2"];

			//First clear all res items
			list = document.formular.sel_ds2_sel_do_res;
			for (var i=list.options.length-1; i>=0; i--) list.options[i]=null;
			document.formular.sel_do_res.value = '';
			//Then add the ones of the newly selected event group
			if(r[gid]["resitems"]) {
				split = r[gid]["resitems"].split("@@@@");
				document.formular.sel_do_res.value = split[0];
				opts = split[1].split("@@@");
				for(var i=0; i<opts.length; i++) {
					opt = opts[i].split("@@");
					document.formular.sel_ds2_sel_do_res.options[i] = new Option(opt[0], opt[1]);
				}
			}

		}//if(res)
	}//if(r[gid])
}//selEventGruppe()

-->
</script>
