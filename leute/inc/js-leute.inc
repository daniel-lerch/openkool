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

			//Select leeren
			list = document.getElementsByName('sel_ds1_input_groups')[0];
			for (var i=list.options.length-1; i>=0; i--) {
				list.options[i]=null;
			}

			//Optionen splitten und einfüllen
			if(responseText != "") {
				var options = responseText.split("#");
				for(i=0; i<options.length; i++) {
					temp = options[i].split(",");
					list.options[i] = new Option(temp[1], temp[0]);
					list.options[i].title = temp[1];
				}
			}

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
	do_var1 = do_var2 = do_var3 = "";
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

	var neg = document.getElementsByName('filter_negativ')[0];
	if(neg && neg.checked == 1) do_neg = true;
	else do_neg = false;

	sendReq('../leute/inc/ajax.php', 'action,var1,var2,var3,neg,sesid', mode+','+do_var1+','+do_var2+','+do_var3+','+do_neg+','+sesid, do_element);
}//do_submit_filter()



$(document).ready(function() {
	// Warning when modifying family fields
	familyFields = {};
	$("td.family_field_with_warning").keypress(function () {
		var name = $(this).children('input').attr('name');
		var value = $(this).children('input').val();
		if (!(name in familyFields)) {
			familyFields[name] = value;
		}
	});
	$("td.family_field_with_warning").keyup(function () {
		var name = $(this).children('input').attr('name');
		var value = $(this).children('input').val();

		var col = $(this).parent().children().index($(this));
		var upper = $($(this).parent().prev().children().get(col));

		if (familyFields[name] != value) {
			upper.children('.family_field_warning').css('visibility', 'visible');
		}
		else if (familyFields[name] == value) {
			upper.children('.family_field_warning').css('visibility', 'hidden');
		}
	});

	//Leere zeilen ein- und ausblenden bei Etiketten Export
	$("div.empty_address_line img.show_button").live('click', function() {
		$(this).hide();
		$(this).prev().show();
		$(this).next().next().show();
		$(this).next().show();
		$(this).parent('div').css('height', 'auto');
	});
	$("div.empty_address_line img.hide_button").live('click', function() {
		$(this).hide();
		$(this).prev().prev().hide();
		$(this).next().hide();
		$(this).prev().show();
		$(this).parent('div').css('height', '0px');
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
	$(".filter-divider").live('click', function(event) {
		$(".filter-group").hide();
		$("#fg"+$(this).attr("id")).show();
	});

	//Scrolling filter titles
	$(".filter-button, .filter-active").live('mouseenter', function() {
    var spanWidth = $(this).find("span").width();
		var maxWidth = $(this).find("div.filter-text").width();
    if(spanWidth > maxWidth) {
      $(this).find("span").stop(true);
      diff = spanWidth - maxWidth + $(this).find("span").position().left;
      t = diff / 0.05;
      $(this).find("span").animate({left: -1*(spanWidth-maxWidth)+"px"}, t, "linear");
    }
  });
  $(".filter-button, .filter-active").live('mouseleave', function() {
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
	$("th.ko_list").live('mouseover', function(event) {
		if($(this).find("span.ko_list_hide").length == 0) {
			if($(this).attr("id") && $(this).attr('id').substring(0, 4) == 'col_') {
				colid = $(this).attr("id").substring(4);
				var new_span = $('<span class="ko_list_hide"/>');
				var new_img = $('<img src="../images/icon_close.gif" border="0" title="<?php print getLL('list_hide_column'); ?>" style="cursor: pointer;" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,id,state,redraw,sesid\', \'itemlist,'+colid+',switch,1,\'+kOOL.sid, do_element);" />');
				new_span.append(new_img);
				$(this).children("span").append(new_span);
			}
		} else {
			$(this).find("span.ko_list_hide").show();
		}
	});
	$("th.ko_list").live('mouseout', function(event) {
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
	clipBoardInit();

	//Changes to main_content: Re-Init clipboard
	$("#main_content").on('DOMSubtreeModified', function() {
		if(this.innerHTML.length > 0) {
			clipBoardInit();
		}
	});

	//Reposition clipboard flash when the overlay is being shown
	$("#main_content").on('mouseover', '.list-edit-overlay', function() {
		ko_zclip.reposition();
	});


	$("#gs_filter").change(function() {
		jumpToUrl('index.php?action=show_groupsubscriptions&gid='+this.value);
	});
});


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
			document.getElementsByName('txt_text')[0].value = split[3] ? split[3] : '';
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
    // Abfrage der Einfügeposition
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    while(!re.test(pos)) {
      pos = prompt("Einfügen an Position (0.." + input.value.length + "):", "0");
    }
    if(pos > input.value.length) {
      pos = input.value.length;
    }
    // Einfügen des Formatierungscodes
    var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
		*/
  }
}//add_markup()



// Initialize clipBoard for copying address to the clipboard
function clipBoardInit() {
	ko_zclip = new ZeroClipboard( $(".clipboardContainer"), { moviePath: "<?php print $ko_path; ?>inc/ZeroClipboard.swf" } );

	ko_zclip.on("mouseover", function(client, args) {
		$(this).closest("div.list_overlay").show();
	});

	ko_zclip.on("mouseup", function(client, args) {
		ko_infobox('INFO', '<?php print getLL('leute_info_address_to_clipboard'); ?>');
	});
}
-->
</script>
