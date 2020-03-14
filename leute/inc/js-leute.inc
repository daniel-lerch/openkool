<script language="javascript" type="text/javascript">
<!--
function printLength(string) {
	num_sms = 1;
  a = document.getElementById("num_letters");
	num = string.length;
	if(num > 160) {
		while(num >= 153) {
			num -= 153;
			num_sms += 1;
		}
	}
	a.innerHTML = num+'/'+160+'<br />#SMS: '+num_sms;
}

function change_fam_image(path) {
	obj = document.getElementById('fam_plus_image');
	if(obj.src.search('disabled') > 0) {
		obj.src = path+"images/icon_arrow_down_big_enabled.png";
	} else {
		obj.src = path+"images/icon_arrow_down_big_disabled.png";
	}
}//change_fam_image()

function select_all_fam_chk() {
	for (i=0; i<document.formular.length;i++) {
	  obj = document.formular.elements[i];
    if (obj.type == "checkbox" && obj.name.substring(0,7) == "famchk[") {
			obj.checked = !obj.checked;
		}
	}
}//select_all_fam_chk()

function select_export_marked() {
  a = document.getElementsByName("sel_auswahl")[0];
	for (var i = 0; i < a.options.length; i++) {
		if (a.options[i].value == 'markierte') {
			a.selectedIndex = i;  //marked
			return
		}
	}
}//select_export_marked()


function fill_grouproles_select(group) {
	sendReq("../groups/inc/ajax.php", "action,group_id,sesid", "grouproleselect,"+group+",<?php print session_id(); ?>", do_fill_grouproles_select);
}//fill_groupsroles_select()

function do_fill_grouproles_select() {
	if(http.readyState == 4) {
		if (http.status == 200) {
			responseText = http.responseText;

			// Remove entries from select
			var $list = $(document.getElementsByName('sel_ds1_input_groups')[0]);
			if($list.length > 0) {
				$list.children().remove();

				// Split options and prepare new html
				var html = '';
				var options = responseText.split("#");
				for(i=0; i<options.length; i++) {
					if (options[i] == '') continue;
					var temp = options[i].split(",");
					var val = temp[0],
						text = temp[1],
						title = temp[2];
					html += getSelectOption(val, text, (title ? 'title="'+title+'"' : null));
				}

				// insert html
				$list.html(html);
			}//if(list)

		}//if(http.status == 200)
		else if (http.status == 404)
			alert("Request URL does not exist");

		//Hide message box
		msg = document.getElementsByName('wait_message')[0];
		msg.style.display = "none";
		document.body.style.cursor = 'default';
  }
}//do_fill_grouproles_select()



function do_submit_filter(mode,sesid) {
	do_var1 = do_var2 = do_var3 = do_var4 = "";
	do_neg = false;

	var var1 = document.getElementsByName('var1')[0];
	if(var1 && var1.type == 'checkbox') {
		if(var1.checked == 1) do_var1 = true;
		else do_var1 = false;
	} else {
		if(var1) do_var1 = var1.value;
	}

	var var2 = document.getElementsByName('var2')[0];
	if(var2 && var2.type == 'checkbox') {
		if(var2.checked == 1) do_var2 = true;
		else do_var2 = false;
	} else {
		if(var2) do_var2 = var2.value;
	}

	var var3 = document.getElementsByName('var3')[0];
	if(var3 && var3.type == 'checkbox') {
		if(var3.checked == 1) do_var3 = true;
		else do_var3 = false;
	} else {
		if(var3) do_var3 = var3.value;
	}

	var var4 = document.getElementsByName('var4')[0];
	if(var4 && var4.type == 'checkbox') {
		if(var4.checked == 1) do_var4 = true;
		else do_var4 = false;
	} else {
		if(var4) do_var4 = var4.value;
	}

	var neg = document.getElementsByName('filter_negativ')[0];
	if(neg && neg.checked == 1) do_neg = true;
	else do_neg = false;

	//do_var1 = encodeURIComponent(do_var1);
	//do_var2 = encodeURIComponent(do_var2);
	//do_var3 = encodeURIComponent(do_var3);

	sendReq('../leute/inc/ajax.php', 'action,var1,var2,var3,var4,neg,sesid', mode+','+do_var1+','+do_var2+','+do_var3+','+do_var4+','+do_neg+','+sesid, do_element);
}//do_submit_filter()



$(document).ready(function() {
	// Warning when modifying family fields
	familyFields = {};
	$(".family_field_with_warning").keypress(function () {
		var name = $(this).children('input').attr('name');
		var value = $(this).children('input').val();
		if (!(name in familyFields)) {
			familyFields[name] = value;
		}
	});
	$(".family_field_with_warning").keyup(function () {
		var name = $(this).children('input').attr('name');
		var value = $(this).children('input').val();

		var label = $(this).closest('.formular-cell').find('.family_field_warning');

		if (familyFields[name] != value) {
			label.css('visibility', 'visible');
		}
		else if (familyFields[name] == value) {
			label.css('visibility', 'hidden');
		}
	});

	//Leere zeilen ein- und ausblenden bei Etiketten Export
	$('.empty-line-element').hide();
	$('.empty-line-element.show_button').show();
	$('body').on('click', ".show_button", function() {
		var row_id = $(this).attr('data-row-id');
		$('.empty-line-element[data-row-id="'+row_id+'"]').show();
		$('input.empty-line-element[data-row-id="'+row_id+'"]').focus();
		$(this).hide();
	});
	$('body').on('click', ".hide_button", function() {
		var row_id = $(this).attr('data-row-id');
		$('input.empty-line-element[data-row-id="'+row_id+'"]').val('');
		$('.empty-line-element[data-row-id="'+row_id+'"]').hide();
		$('.empty-line-element.show_button[data-row-id="'+row_id+'"]').show();
	});
	// Return address in 'etiketten export'
	$("input[name='chk_return_address']").on('click', function() {
		if (this.checked) {
			$("#extended_return_address").show();
		}
		else {
			$("#extended_return_address").hide();
		}
	});
	$("select[name='sel_return_address']").on('change', function() {
		if (this.value.indexOf('manual_address') > -1) {
			$("#manual_return_address").show();
		}
		else {
			$("#manual_return_address").hide();
		}
	})

	//Accordion for filter groups
	$('body').on('click', ".filter-divider", function(event) {
		$(".filter-group").hide();
		$("#fg"+$(this).attr("id")).show();
	});

	//Scrolling filter titles
	$('body').on('mouseenter', ".filter-button, .filter-active", function() {
    var spanWidth = $(this).find("span").width();
		var maxWidth = $(this).find("div.filter-text").width();
    if(spanWidth > maxWidth) {
      $(this).find("span").stop(true);
      diff = spanWidth - maxWidth + $(this).find("span").position().left;
      t = diff / 0.05;
      $(this).find("span").animate({left: -1*(spanWidth-maxWidth)+"px"}, t, "linear");
    }
  });
  $('body').on('mouseleave', ".filter-button, .filter-active", function() {
    var spanWidth = $(this).find("span").width();
		var maxWidth = $(this).find("div.filter-text").width();
    if(spanWidth > maxWidth) {
      $(this).find("span").stop(true);
      diff = -1 * $(this).find("span").position().left;
      t = diff / 0.05;
      $(this).find("span").animate({left: "0px"}, t, "linear");
    }
  });

	//Mouseover for columns headers
	$('body').on('mouseover', "th.ko_list", function(event) {
		if($(this).find("span.ko_list_hide").length == 0) {
			if($(this).attr("id") && $(this).attr('id').substring(0, 4) == 'col_') {
				colid = $(this).attr("id").substring(4);
				var new_span = $('<span class="ko_list_hide"/>');
				var new_img = $('<img src="../images/icon_close.gif" border="0" title="<?php print getLL('list_hide_column'); ?>" style="cursor: pointer;" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,id,state,redraw,sesid\', \'itemlist,'+colid+',switch,1,\'+kOOL.sid, do_element);" />');
				new_span.append(new_img);
				$(this).append(new_span);
			}
		} else {
			$(this).find("span.ko_list_hide").show();
		}
	});
	$('body').on('mouseout', "th.ko_list", function(event) {
		$(this).find("span.ko_list_hide").hide();
	});


	//GroupTree
	$("#sm_leute_itemlist_spalten").on("click", "li.gtree", function(e) {
		id = $(this).attr('id');
		ul = $(this).children('ul');

		if($(ul).is(':visible')) {
			$(ul).addClass('gtree_state_closed');
			$(this).addClass('gtree_state_closed');
		} else {
			$(ul).removeClass('gtree_state_closed');
			ilist = $(this).closest("div.itemlist");
			//ScrollTop only works in webkit, not in Firefox
			if(jQuery.browser.webkit) $(ilist).scrollTop($(ilist).scrollTop()+Math.min($(ul).height(), $(ilist).height()-e.offsetY-30));
			$(this).removeClass('gtree_state_closed');
		}
		e.stopPropagation();
	});

	//Itemlist groupTree
	$("#sm_leute_itemlist_spalten").on("click", ".itemlist_chk", function(e) {
		id = $(this).attr('id');
		sendReq('../leute/inc/ajax.php', 'action,id,state,sesid', 'itemlist,'+id+','+this.checked+','+kOOL.sid, do_element);
		e.stopPropagation();
	});


	$("body").on("click", "#fp_alias_switch", function() {
		if($("#fp_alias_container").is(':visible')) {
			$("#fp_alias_container").hide();
		} else {
			$("#fp_alias_container").show();
		}
	});



	//Init clipboard the first time
	<?php if (ko_get_userpref($_SESSION['ses_userid'], 'enable_zero_clipboard')) { ?>
	clipBoardInit();
	<?php } ?>


	$('body').on('mouseleave', 'td.list-edit-overlay', function() {
		$(this).find('.list_overlay').hide();
	});


	$("#gs_filter").change(function() {
		jumpToUrl('index.php?action=groupsubscriptions&gid='+this.value);
	});


	// leute crm entries
	$('body').on('click', '.leute-crm-btn', function() {
		var id = $(this).attr('data-id');
		change_crm_div(id, 'toggle');
		return false;
	});
	$('body').on('click', '.leute-crm-entries-add-entry-form-btn', function() {
		var leute_id = $(this).closest('.ko-js-table-container').attr('data-parent-row-id');
		var target = '#leute-crm-entries-'+leute_id;
		$.get('inc/ajax.php', {
			action: 'addcrmentry',
			leute_id: leute_id,
			sesid: kOOL.sid
		}, function(data) {
			if (data) {
				ko_js_table_remove_form_rows(target);
				$(target).children('tbody').children('tr:first-child').after(data);
			}
		});
	});
	$('body').on('click', '.leute-crm-entries-add-entry-submit-btn', function() {
		var leute_id = $(this).closest('.ko-js-table-container').attr('data-parent-row-id');
		var target = '#leute-crm-entries-'+leute_id;
		var submit_object = new FormData($('form[name="formular"]')[0]);
		submit_object.append('action', 'submitaddcrmentry');
		submit_object.append('leute_id', leute_id);
		submit_object.append('sesid', kOOL.sid);
		$.ajax(
			{
				url: 'inc/ajax.php',
				type: 'POST',
				data: submit_object,
				async: false,
				cache: false,
				contentType: false,
				processData: false
			}
		).done(
			function(data) {
				if (data) {
					$(target).children('tbody').children('.ko-js-table-data-row').remove();
					$(target).children('tbody').children('tr:first-child').after(data);
					ko_js_table_hide_unnecessary_filters(target);
				}
			}
		).always(
			function(data) {
				ko_js_table_apply_filters(target);
			}
		);
	});
	$('body').on('keypress', '.ko-js-table-form-row', function(e) {
		if (e.which == 13) {
			var btn = $(this).find('.leute-crm-entries-edit-entry-submit-btn');
			if (btn.length == 0) btn = $(this).find('.leute-crm-entries-add-entry-submit-btn');
			btn.click();
			return false;
		}
		else if (e.which == 27) {
			var btn = $(this).find('.leute-crm-entries-exit-form-btn');
			btn.click();
			return false;
		}
	})
	$('body').on('click', '.leute-crm-entries-edit-entry-form-btn', function() {
		var leute_id = $(this).closest('.ko-js-table-container').attr('data-parent-row-id');
		var contact_id = $(this).closest('.ko-js-table-data-row').attr('data-id');
		var target = '#leute-crm-entries-'+leute_id;
		var row = $(target).find('.ko-js-table-data-row[data-id="'+contact_id+'"]');
		$.get('inc/ajax.php', {
			action: 'editcrmentry',
			leute_id: leute_id,
			contact_id: contact_id,
			sesid: kOOL.sid
		}, function(data) {
			if (data) {
				ko_js_table_remove_form_rows(target);
				row.addClass('ko-js-table-data-row-form-hidden');
				row.before(data);
			}
		});
	});
	$('body').on('click', '.leute-crm-entries-edit-entry-submit-btn', function() {
		var leute_id = $(this).closest('.ko-js-table-container').attr('data-parent-row-id');
		var contact_id = $(this).closest('.ko-js-table-form-row').attr('data-id');
		var target = '#leute-crm-entries-'+leute_id;
		var submit_object = new FormData($('form[name="formular"]')[0]);
		submit_object.append('action', 'submiteditcrmentry');
		submit_object.append('leute_id', leute_id);
		submit_object.append('contact_id', contact_id);
		submit_object.append('sesid', kOOL.sid);
		$.ajax(
			{
				url: 'inc/ajax.php',
				type: 'POST',
				data: submit_object,
				async: false,
				cache: false,
				contentType: false,
				processData: false
			}
		).done(
			function(data) {
				if (data) {
					$(target).children('tbody').children('.ko-js-table-data-row').remove();
					$(target).children('tbody').children('tr:first-child').after(data);
					ko_js_table_hide_unnecessary_filters(target);
				}
			}
		).always(
			function(data) {
				ko_js_table_apply_filters(target);
			}
		);
	});
	$('body').on('click', '.leute-crm-entries-exit-form-btn', function() {
		var leute_id = $(this).closest('.ko-js-table-container').attr('data-parent-row-id');
		var target = '#leute-crm-entries-'+leute_id;
		ko_js_table_remove_form_rows(target);
	});
	$('body').on('click', '.leute-crm-entries-delete-entry-btn', function() {
		var c = confirm($(this).attr('data-confirm-delete-label'));
		if (!c) return;

		var leute_id = $(this).closest('.ko-js-table-container').attr('data-parent-row-id');
		var contact_id = $(this).closest('.ko-js-table-data-row').attr('data-id');
		var target = '#leute-crm-entries-'+leute_id;
		var row = $(target).find('.ko-js-table-data-row[data-id="'+contact_id+'"]');
		$.get('inc/ajax.php', {
			action: 'deletecrmentry',
			leute_id: leute_id,
			contact_id: contact_id,
			sesid: kOOL.sid
		}, function(data) {
			if (data) {
				$(target).children('tbody').children('.ko-js-table-data-row').remove();
				$(target).children('tbody').children('tr:first-child').after(data);
			}
			ko_js_table_apply_filters(target);
		});
	});
	$('body').on('change', '.crm-cruser-filter-select', function() {
		var leute_id = $(this).closest('.ko-js-table-container').attr('data-parent-row-id');
		var container = $(this).closest('.ko-js-table-container');
		var target = '#leute-crm-entries-'+leute_id;
		var admingroups_item = container.find('.crm-cruser-filter-admingroups-item');
		var cruser_item = container.find('.crm-cruser-filter-cruser-item');

		var val = $(this).val();
		if (val.substr(0, 1) == 'a') {
			admingroups_item.attr('data-filter-value', val);
			admingroups_item.addClass('active');
			cruser_item.attr('data-filter-value', '');

		} else {
			cruser_item.attr('data-filter-value', val);
			cruser_item.addClass('active');
			admingroups_item.attr('data-filter-value', '');
		}
		if (!cruser_item.attr('data-filter-value')) {
			cruser_item.removeClass('active');
		}
		if (!admingroups_item.attr('data-filter-value')) {
			admingroups_item.removeClass('active');
		}

		ko_js_table_apply_filters(target);
	});

	if(kOOL.module == 'leute') {
		$('.richtexteditor').ckeditor({customConfig : '/leute/inc/ckeditor_custom_config.js' });
	}

	$('.family-relative-id').peoplesearch({
		removalWarning: kOOL_ll.relative_removal_warning,
		showRemovalWarning: true,
		multiple: false
	});
	$('input').on('peoplesearch.remove', function(id) {
		update_families();
	});
	$('input').on('peoplesearch.add', function(id) {
		update_families();
	});
	$('body').on('click', '.btn-sel-family', function() {
		var $this = $(this);
		$('#sel_familie').val($this.attr('data-id'));
		update_family();
		var $famfunction = $('[name="input_famfunction"]');
		if (!$famfunction.val()) {
			if ($this.data('member-id') == $('[name="input_father"]').val() || $this.data('member-id') == $('[name="input_mother"]').val()) {
				$famfunction.val('child');
			}
		}
		clear_families();
		$('[name="hid_new_family"]').val('0').change();
	});
	$('body').on('change', '#sel_familie', function() {
		update_family();
		update_families();
		$('[name="hid_new_family"]').val('0').change();
	});
	$('[name="hid_new_family"]').change(function() {
		var $this = $(this);
		var $sel = $('#sel_familie');
		var $dummy = $sel.children('[value=""]');
		var newText = $sel.data('text-new');
		var noneText = $sel.data('text-none');
		if ($this.val() == '1') {
			$sel.children('[value="0"]').html(newText);
		}
		else {
			$sel.children('[value="0"]').html(noneText);
		}
	});
	update_families();


	// automatically fill geschlecht / anrede if other is selected and it is still empty
	$('[name="input_geschlecht"], [name="input_anrede"]').on('change', function() {
		checkTitleAndGeschlecht($(this));
	});
});


function update_families() {
	var el = $('#sel_familie');
	var ids = [];
	$('.family-relative-id').each(function() {
		var id = $(this).val();
		if (id) ids.push(id);
	});

	var memberids = '';
	if (ids.length > 0) memberids = ids.join(',');

	clear_families();

	if (el.val() == '0' && ids.length > 0) {
		$.get(
			"inc/ajax.php",
			{
				action: "getfamilies",
				memberids: memberids,
				sesid: kOOL.sid
			}, function(data) {
				var json = JSON.parse(data);
				var html = '';
				for (id_ in json) {
					var family = json[id_];
					html += '<button type="button" class="btn btn-sm btn-default btn-sel-family" data-id="'+family.id+'" style="width:220px;" data-member-id="'+family.memberid+'" title="'+family.title+'"><span class="pull-left" style="width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+family.desc+'</span><i class="fa fa-check text-success pull-right icon-line-height"></i></button>';
				}
				if (html) {
					$('#household-suggestions-container').show();
					$('#household-suggestions').html(html);
				}
			}
		);
	}
}
function clear_families() {
	$('#household-suggestions').html('');
	$('#household-suggestions-container').hide();
}
function update_family() {
	$('form[name="formular"]').children('.alert.alert-warning.alert-dismissible').remove();
	$('form[name="formular"]').prepend('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>'+leute_warning_family_fields_changed+'</div>');

	var el = $('#sel_familie');

	if (el.val() > 0) {
		$.get(
			"inc/ajax.php",
			{
				action: "getfamily",
				famid: el.val(),
				sesid: kOOL.sid
			}, function(data) {
				var json = JSON.parse(data);
				for (var name in json) {
					var value = json[name];
					$('[name="'+name+'"]').setVal(value);
				}
				$('form[name="formular"]').children('.alert.alert-warning.alert-dismissible').remove();
				$('form[name="formular"]').prepend('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>'+leute_warning_family_fields_changed+'</div>');
			}
		)
	}
}

// This function is called when the new-family button is clicked
function clear_family() {
	//$('.leute-family-field, #fam_content').find('input, select').val('');
	$('#sel_familie').val('0');
}




function change_crm_div(id, mode, callback) {
	var container = $('#crm_tr_'+id);
	var crm_div = $('#crm_'+id);
	var target = '#leute-crm-entries-'+id;

	if (!mode) mode = 'toggle';
	if (mode == 'hide' || (mode == 'toggle' && container.css('display') != 'none')) {
		container.hide();
		if (callback) callback();
	}
	else if (mode == 'show' || (mode == 'toggle' && container.css('display') == 'none')) {
		if(crm_div.children().length == 0) {
			$.get(
				'inc/ajax.php',
				{
					action: 'crm',
					id: id,
					sesid: kOOL.sid
				},
				function(data) {
					if (data) {
						crm_div.html(data);
						ko_js_table_hide_unnecessary_filters(target);
					}
					container.show();
					if (callback) callback();
				}
			);
		} else {
			container.show();
			if (callback) callback();
		}
	}
}


<?php
$title2GeschlechtMap = $LEUTE_TITLE_TO_SEX[$_SESSION['lang']];
array_walk_recursive($title2GeschlechtMap, 'utf8_encode_array');

$geschlecht2TitleMap = array();
foreach ($title2GeschlechtMap as $t => $s) {
	if (!isset($geschlecht2TitleMap[$s])) $geschlecht2TitleMap[$s] = $t;
}

?>

var title2GeschlechtMap = <?php print json_encode($title2GeschlechtMap); ?>;
var geschlecht2TitleMap = <?php print json_encode($geschlecht2TitleMap); ?>;
function checkTitleAndGeschlecht($this) {
	var $title = $('[name="input_anrede"]');
	var $geschlecht = $('[name="input_geschlecht"]');

	if ($this.attr('name') == 'input_anrede' && !$geschlecht.val() && $title.val() && title2GeschlechtMap[$title.val()]) {
		$geschlecht.val(title2GeschlechtMap[$title.val()]);
	} else if ($this.attr('name') == 'input_geschlecht' && !$title.val() && $geschlecht.val() && geschlecht2TitleMap[$geschlecht.val()]) {
		$title.val(geschlecht2TitleMap[$geschlecht.val()]);
	}
}



function filter_crm_project(target, id) {
	$('.ko-js-table-filter-item[data-filter-target="'+target+'"]').removeClass('active').addClass('label-default').removeClass('label-danger');
	$('.ko-js-table-filter-item[data-filter-target="'+target+'"][data-filter-col="project_id"][data-filter-value="'+id+'"]').addClass('active').addClass('label-danger').removeClass('label-default');
	ko_js_table_apply_filters(target);
}




function do_update_df_form(id, sesid) {
	groups = document.getElementsByName('input_groups')[0].value;
	while(groups.indexOf(",") != -1) groups = groups.replace(",", "A");
	sendReq('../leute/inc/ajax.php', 'action,groups,id,sesid', 'updatedfform,'+groups+','+id+','+sesid, do_element);
}


function mailmerge_reuse() {
	if(http.readyState == 4) {
		if (http.status == 200) {
			responseText = http.responseText;

			//get element id and values
			split = responseText.split("@@@");

			if(split[1] == 'informal' || split[1] == undefined) {
				document.getElementsByName('rd_salutation')[0].checked = 'checked';
				document.getElementsByName('rd_salutation')[1].checked = '';
			} else {
				document.getElementsByName('rd_salutation')[0].checked = '';
				document.getElementsByName('rd_salutation')[1].checked = 'checked';
			}
			document.getElementsByName('txt_subject')[0].value = split[2] ? split[2] : '';
			// set text of ckeditor
			for(var i in CKEDITOR.instances) {
				if (CKEDITOR.instances[i].name == 'txt_text') {
					CKEDITOR.instances[i].setData(split[3] ? split[3] : '');
				}
			}
			document.getElementsByName('txt_closing')[0].value = split[4] ? split[4] : '';
			document.getElementsByName('txt_signature')[0].value = split[5] ? split[5] : '';
			if(split[6] == '0' || split[6] == undefined) {
				document.getElementsByName('chk_sig_file')[0].checked = '';
			} else {
				document.getElementsByName('chk_sig_file')[0].checked = 'checked';
			}

		}//if(http.status == 200)
		else if (http.status == 404)
			alert("Request URL does not exist");

		//Hide message box
		msg = document.getElementsByName('wait_message')[0];
		msg.style.display = "none";
		document.body.style.cursor = 'default';
  }

}//mailmerge_reuse()



function add_markup(input, code) {
	aTag = '['+code+']';
	eTag = '[/'+code+']';

  input.focus();
  // for IE
  if(typeof document.selection != 'undefined') {
    // Enter code
    var range = document.selection.createRange();
    var insText = range.text;
    range.text = aTag + insText + eTag;
    // Set cursor's position
    range = document.selection.createRange();
    if (insText.length == 0) {
      range.move('character', -eTag.length);
    } else {
      range.moveStart('character', aTag.length + insText.length + eTag.length);      
    }
    range.select();
  }
  //For Gecko browsers (Mozilla etc)
  else if(typeof input.selectionStart != 'undefined')
  {
    // Enter code
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
    // Set cursor's position
    var pos;
    if (insText.length == 0) {
      pos = start + aTag.length;
    } else {
      pos = start + aTag.length + insText.length + eTag.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  //Other browsers
  else
  {
		/*
    // Abfrage der Einfuegeposition
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    while(!re.test(pos)) {
      pos = prompt("Einfuegen an Position (0.." + input.value.length + "):", "0");
    }
    if(pos > input.value.length) {
      pos = input.value.length;
    }
    // Einfuegen des Formatierungscodes
    var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
		*/
  }
}//add_markup()



// Initialize clipBoard for copying address to the clipboard

function clipBoardInit() {

	ZeroClipboard.config({
		swfPath: "<?php print $ko_path; ?>inc/ZeroClipboard.swf"
	});

	var ko_zclip = new ZeroClipboard();

	ko_zclip.on("copy", function() {
		ko_infobox('INFO', '<?php print getLL('leute_info_address_to_clipboard'); ?>');
	});

	$('body').on('mouseenter', '.list-edit-overlay', function(){
		var el = $(this).find('.clipboardContainer');
		var text = (el).data('clipboard-text');
		ko_zclip.clip($(el).parent('button'));
		ko_zclip.setText(text);
	});
}





-->
</script>
