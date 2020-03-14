function set_ids_from_chk(el) {
	var ids = '';
	$.each($("input[name^='chk[']:checked"), function(key, obj) {
		ids += obj.name.replace('chk[', '').replace(']', '')+',';
	});
	ids = ids.slice(0, -1);
	set_hidden_value('ids', ids, el);
}


function set_action(a, el) {
	if(el == null) {  //Backwards compatibility
		document.getElementById("action").value = a;
	} else {
		jQuery(el).closest('form').find('input[name="action"]').attr('value', a);
	}
}//set_action()


function set_hidden_value(name, value, el) {
	if(el == null) {  //Backwards compatibility
		document.getElementsByName(name)[0].value = value;
	} else {
		jQuery(el).closest('form').find('input[name="'+name+'"]').attr('value', value);
	}
}//set_res_code()


function double_select_add(text, value, name, hid_name) {
	var $list = $(document.getElementsByName(name)[0]);

	// Don't allow double entries
	var exit = false;
	$list.children().each(function() {
		if ($(this).data('value') == value) exit = true;
	});
	if (exit) return;

	// Add new element
	$list.append(getSelectOption(value, text));

	// Update hidden value
	var $hid = $(document.getElementsByName(hid_name)[0]);
	var vals = [];
	$list.children().each(function() {
		if ($(this).data('value')) vals.push($(this).data('value'));
	});
	$hid.val(vals.join(','));
}//double_select_add()


function dateselect_select_fill(name, values) {
	var list = document.getElementsByName(name)[0];
	$(list).empty();
	for (i = 0; i < values.length; i++) {
		if (values[i] == '') continue;
		var neu = new Option(values[i], '');
		list.options[list.length] = neu;
	}
}// dateselect_select_fill()



function double_select_move(name, mode) {
	var $list = $(document.getElementsByName(('sel_ds2_'+name))[0]);
	var $children = $list.children();
	var $activeChildren = $children.filter('.'+doubleSelectActiveClass);
		//delete
	if(mode == "del") {
		$activeChildren.remove();
	} else {
		//move modes
		if ($activeChildren.length != 1) return;
		var activeElement = $activeChildren[0];
		var $activeElement = $(activeElement);
		var activeIndex = $children.index(activeElement);

		if(mode == "top") {
			$activeElement.detach().prependTo($list);
		} else if(mode == "up") {
			if (activeIndex == 0) return;
			var $prev = $activeElement.prev();
			$activeElement.detach().insertBefore($prev);
		} else if(mode == "down") {
			if (activeIndex == $children.length - 1) return;
			var $next = $activeElement.next();
			$activeElement.detach().insertAfter($next);
		} else if(mode == "bottom") {
			$activeElement.detach().appendTo($list);
		}
	}

	// Update hidden value
	var hid = $(document.getElementsByName(name)[0]);
	var vals = [];
	$list.children().each(function() {
		if ($(this).data('value')) vals.push($(this).data('value'));
	});
	hid.val(vals.join(','));
}//double_select_move()



function do_fill_select() {
	if(http.readyState == 4) {
		if (http.status == 200) {
			responseText = http.responseText;

			// get element id and values
			var split = responseText.split("@@@");
			var el_id = split[0].trim();
			var value = split[1];

			// Remove entries from select
			var $list = $(document.getElementsByName(el_id)[0]);
			if($list.length > 0) {
				$list.children().remove();

				// Split options and prepare new html
				var html = '';
				var options = value.split("#");
				for(i=0; i<options.length; i++) {
					var temp = options[i].split(",");
					var val = temp[0],
						text = temp[1],
						title = temp[2];
					html += getSelectOption(val, text, (title ? 'title="'+title+'"' : ''));
				}

				// insert html
				$list.html(html);
			}//if(list)

		}//if(http.status == 200)
		else if (http.status == 404)
			alert("Request URL does not exist");

		//Message-Box ausblenden
		msg = document.getElementsByName('wait_message')[0];
		msg.style.display = "none";
		document.body.style.cursor = 'default';
  }
}//do_fill_select()



function change_vis(obj_name) {
  obj = document.getElementById(obj_name);
  if(obj.style.visibility == "hidden") {
    obj.style.visibility = "visible";
    obj.style.display = "block";
  } else {
    obj.style.visibility = "hidden";
    obj.style.display = "none";
  }
}//change_vis()

function change_vis_tr(obj_name) {
  obj = document.getElementById(obj_name);
  if(obj.style.display == "none") {
    obj.style.display = "";
  } else {
    obj.style.display = "none";
  }
}//change_vis()


function set_vis(obj_name) {
  obj = document.getElementById(obj_name);
  obj.style.visibility = "visible";
  obj.style.display = "block";
}//set_vis()


function unset_vis(obj_name) {
  obj = document.getElementById(obj_name);
  obj.style.visibility = "hidden";
  obj.style.display = "none";
}//unset_vis()


function open_panel(id) {
	$(id).children('.collapse').collapse('show');
}//open_panel()


function close_panel(id) {
	$(id).children('.collapse').collapse('hide');
}//close_panel()





function select_all_list_chk() {
	for (i=0; i<document.formular.length;i++) {
	  obj = document.formular.elements[i];
    if (obj.type == "checkbox" && obj.name.substring(0,4) == "chk[") {
			obj.checked = !obj.checked;
		}
		else if (obj.type == "text" && obj.name.substring(0,4) == "txt[") {
			if(!obj.value) obj.value = 1;
			else obj.value = Math.abs(obj.value)+1;
		}
	}
}//select_all_list_chk()


function openPic(url,winName,winParams)	{
	var theWindow = window.open(url,winName,winParams);	
	if (theWindow)	{theWindow.focus();}
}


function jumpToUrl(URL)	{
	document.location = URL;
}

String.prototype.trim = function() {
	return this.replace(/^\s*|\s*$/g, "");
}


function getMultiple(ob) {
	selected = "";
	while (ob.selectedIndex != -1) {
		selected += ob.options[ob.selectedIndex].value+"MULTIPLE";
		ob.options[ob.selectedIndex].selected = false;
	}
	selected = selected.slice(0, -8);
	return selected;
}


function exchangeComma(ob) {
	if(ob.value.match(";")) {
		while(ob.value.match(";")) ob.value = ob.value.replace(";", ",");
	} else {
		while(ob.value.match(",")) ob.value = ob.value.replace(",", ";");
	}
}//exchangeComma()



function form_set_first_input() {
	for(i=0; i<document.formular.length; i++) {
		obj = document.formular.elements[i];
		if(obj.type != "hidden" && obj.name.substr(0, 3) == "koi") {
			obj.focus();
			return true;
		}
	}

	//no koi found, so try first not-hidden form element
	for(i=0; i<document.formular.length; i++) {
		obj = document.formular.elements[i];
		if(obj.type != "hidden" && obj.name != "sel_notiz" && obj.name != "txt_notiz" && obj.name != "txt_notiz_new") {
			obj.focus();
			return true;
		}
	}

}//form_set_first_input()


function form_set_focus(name) {
  obj = document.getElementsByName(name)[0];
	obj.focus();
}//form_set_focus()



function forAllHeader() {
	var checkbox = $('.forall-checkbox').get(0);
	var open = checkbox.checked;

	var forallGroup = $('.forall-group')[0];

	if (open) {
		$(forallGroup).show();
		$('.group-collapse.collapse').collapse('hide');
	}
	else {
		$(forallGroup).hide();
		$('.group-collapse.collapse').collapse('show');
	}
}//forAllHeader()










/********************* Ajax *****************************/
var http;

function sendReq(serverFileName, variableNames, variableValues, handleResponse) {
	if (!(variableNames instanceof Array)) {
		variableNames = variableNames.split(',');
	}
	if (!(variableValues instanceof Array)) {
		variableValues = variableValues.split(',');
	}
	params = {};

	for(i=0; i<variableNames.length; i++) {
		params[variableNames[i]] = variableValues[i];
	}

	http = $.get(serverFileName,params,handleResponse);
}


/**
  * Zeichnet ein Element neu
	* Rückgabewert muss so aussehen: ID@@@HTML
	* ID ist die ID des HTML-Elementes (z.B. div), HTML ist der ganze Code
	*/
function do_element() {

	//Loading, Loaded, Interactive
	if(http.readyState == 1 || http.readyState == 2 || http.readyState == 3) {
		//Message-Box einblenden
		msg = document.getElementsByName('wait_message')[0];
    msg.style.display = "block";
		document.body.style.cursor = 'wait';

	//Complete
	} else if(http.readyState == 4) {
		if(http.status == 200) {
			responseText = http.responseText;

			//Show error or info box
			if(responseText.substring(0, 8) == "ERROR@@@" || responseText.substring(0, 7) == 'INFO@@@') {
				split = responseText.split("@@@");
				mode = split[0].trim();
				value = split[1];
				ko_infobox(mode, value);

				//Hide ajax box
				msg = document.getElementsByName('wait_message')[0];
				msg.style.display = "none";
				document.body.style.cursor = 'default';

				return;
			}

			//Direct download
			if(responseText.substring(0, 11) == "DOWNLOAD@@@") {
				split = responseText.split("@@@");
				value = split[1];
				ko_popup('../download.php?action=file&file='+value);

				//Hide ajax box
				msg = document.getElementsByName('wait_message')[0];
				msg.style.display = "none";
				document.body.style.cursor = 'default';

				return;
			}

			//find post-processing directives at the end of return value
			postsplit = responseText.split("@@@POST@@@");
			responseText = postsplit[0];
			do_element_post = postsplit[1];

			//Element-ID und neuen Content holen
			split = responseText.split("@@@");
			var el_counter = 0;
			while (split[el_counter]) {
				el_id = split[el_counter].trim();
				value = split[el_counter + 1];

				//Element neu füllen
				element = document.getElementsByName(el_id)[0];
				if(element) $(element).html(value);

				el_counter = el_counter + 2;
			}

			//post processing for group filter
			if(do_element_post == "filter_group") {
				initList(1, document.getElementsByName('sel1-var1')[0]);
			} else {
				eval(do_element_post);
			}
			
		}//if(http.status == 200)
		else if (http.status == 404) {
			alert("Request URL does not exist");
		}

		//Message-Box ausblenden
		msg = document.getElementsByName('wait_message')[0];
		msg.style.display = "none";
		document.body.style.cursor = 'default';
  }
}//do_element()




function show_box() {

	//Loading, Loaded, Interactive
	if(http.readyState == 1 || http.readyState == 2 || http.readyState == 3) {
		//Message-Box einblenden
		msg = document.getElementsByName('wait_message')[0];
    msg.style.display = "block";
		document.body.style.cursor = 'wait';

	//Complete
	} else if(http.readyState == 4) {
		if(http.status == 200) {
			responseText = http.responseText;

			if(responseText.substring(0, 8) == "ERROR@@@") {
				split = responseText.split("@@@");
				mode = split[0].trim();
				value = split[1];
				ko_infobox(mode, value);

				//Hide ajax box
				msg = document.getElementsByName('wait_message')[0];
				msg.style.display = "none";
				document.body.style.cursor = 'default';

				return;
			}

			//Find size for popup after @@@
			if(responseText.indexOf('@@@') == -1) {  //No @@@ in string, so just url given
				url = responseText.split("@@@");
				x = y = '';
			} else {
				split = responseText.split("@@@");
				url = split[0].trim();
				x = split[1].trim();
				y = split[2].trim();
			}

			//Show given URL in JS popup
			ko_popup(url, x, y);

		}//if(http.status == 200)
		else if (http.status == 404) {
			alert("Request URL does not exist");
		}

		//Message-Box ausblenden
		msg = document.getElementsByName('wait_message')[0];
		msg.style.display = "none";
		document.body.style.cursor = 'default';
  }
}//show_box()



/********************* /Ajax ****************************/




if(typeof jQuery != 'undefined') {
	$(document).ready(function() {

    	$.getScript( "/inc/tooltip.js");

		$(document).click(function() {
			$('.popover[role="tooltip"].in').remove();
		});

		/* Input element switch */
		$(".input_switch").click(function() {
			if($(this).hasClass("switch_state_0")) {
				$(this).removeClass("switch_state_0");
				$(this).addClass("switch_state_1");
				$(this).children(".switch_state_label_0").hide();
				$(this).children(".switch_state_label_1").show();
				$("#"+$(this).attr("name").slice(7)).attr('value', 1);
			} else {
				$(this).removeClass("switch_state_1");
				$(this).addClass("switch_state_0");
				$(this).children(".switch_state_label_1").hide();
				$(this).children(".switch_state_label_0").show();
				$("#"+$(this).attr("name").slice(7)).attr('value', 0);
			}
		});


		/* Click actions for lists: Check checkbox */
		$('body').on("click", "table.ko_list td", function() {
			fullid = $(this).attr("id");
			if(typeof(fullid) != "undefined") {
				temp = fullid.split("|");
				table = temp[0]; id = temp[1]; col = temp[2];
				if(id > 0) {
					if($("#chk\\["+id+"\\]").prop("checked")) $("#chk\\["+id+"\\]").prop("checked", false);
					else $("#chk\\["+id+"\\]").prop("checked", true);
				}
			}
		});

		/* Double click on list table cell: Show edit input */
		$('body').on("dblclick", "table.ko_list td", function() {
			//Hide all open inline forms (only happens for cells with more than one input)
			$("div.inlineform").each(function() {
				//TODO: This doesn't seem to work, but would be better than to just hide the whole div (see bug #16)
				//fullid = $(this).attr("id").slice(3);
				//sendReq("../inc/ajax.php", "action,id,module,sesid", "inlineformblur,"+fullid+","+kOOL.module+","+kOOL.sid, inlineform_show);
				$(this).hide();
			});

			fullid = $(this).attr("id");
			temp = fullid.split("|");
			table = temp[0]; id = temp[1]; col = temp[2];
			if(table != "" && id > 0 && col != "") {
				sendReq("../inc/ajax.php", "action,id,module,sesid", "inlineform,"+fullid+","+kOOL.module+","+kOOL.sid, inlineform_show);
			}
		});

		/* Blur inline form element -> Don't store changes */
		$('body').on('blur', ".inlineform > textarea, .inlineform > input, .inlineform > select, .inlineform > div.input-group", function() {
			var blurredElem = $(this);
			setTimeout(function() {
				if (blurredElem.find(':focus').length > 0) return;
				if (blurredElem.hasClass("if-noblur")) return;
				fullid = blurredElem.parents(".inlineform").attr("id").slice(3);
				sendReq("../inc/ajax.php", "action,id,module,sesid", "inlineformblur,"+fullid+","+kOOL.module+","+kOOL.sid, inlineform_show);
			}, 30);
		});

		/* Prevent double click in textarea and input to reload inline editing */
		$('body').on('dblclick', ".inlineform textarea, .inlineform input", function(event) {
			event.cancelBubble;
			event.returnValue = false;
			if(this.is_ie === false) event.preventDefault();
			return false;
		});

		/* Prevent click in textarea and input to trigger checkbox selection for whole row */
		$('body').on('click', ".inlineform textarea, .inlineform input, .inlineform button", function(event) {
			if($(this).hasClass('if_submit')) {
				//Submit button: Continue normally
			} else {
				event.cancelBubble;
				event.returnValue = false;
				if(this.is_ie === false) event.preventDefault();
				return false;
			}
		});


		/* KeyPress: ENTER stores changes, ESC quites editing */
		$('body').on('keyup', ".inlineform textarea, .inlineform input, .inlineform select", function(event) {
			fullid = $(this).parents(".inlineform").attr("id").slice(3);

			var prevent = false;
			if(event.which == 27) {  //ESC
				//Just redraw table cell without storing changes
				sendReq("../inc/ajax.php", "action,id,module,sesid", "inlineformblur,"+fullid+","+kOOL.module+","+kOOL.sid, inlineform_show);
				prevent = true;
			}
			else if(event.which == 13 && event.shiftKey == false) {  //Enter
				inlineform_submit(this, fullid);
				prevent = true;
			}

			if (prevent) {
				event.cancelBubble;
				event.returnValue = false;
				if(this.is_ie === false) event.preventDefault();
				return false;
			}
		});
		/* Prevent Enter key from submitting form. keyup above is needed to properly detect ESC
		 in all browsers, but then keydown submits form before keyup is handled, where preventions don't work anymore */
		$('body').on('keydown', ".inlineform textarea, .inlineform input, .inlineform select", function(event) {
			fullid = $(this).parents(".inlineform").attr("id").slice(3);

			var prevent = false;
			if(event.which == 27) {  //ESC
				//Just redraw table cell without storing changes
				sendReq("../inc/ajax.php", "action,id,module,sesid", "inlineformblur,"+fullid+","+kOOL.module+","+kOOL.sid, inlineform_show);
				prevent = true;
			}
			else if(event.which == 13 && event.shiftKey == false) {  //Enter
				inlineform_submit(this, fullid);
				prevent = true;
			}

			if (prevent) {
				event.cancelBubble;
				event.returnValue = false;
				if(this.is_ie === false) event.preventDefault();
				return false;
			}
		});


		/* Select changes -> Store changes */
		$('body').on('change', ".inlineform select", function(event) {
			//Don't react to change for doubleselect input
			if($(this).parents(".inlineform").hasClass("if-doubleselect")) return;

			fullid = $(this).parents(".inlineform").attr("id").slice(3);
			inlineform_submit(this, fullid);
		});

		/* Submit button for doubleselect forms clicked -> Store */
		$('body').on('click', ".inlineform button.if_submit", function(event) {
			fullid = $(this).parents(".inlineform").attr("id").slice(3);
			inlineform_submit(this, fullid);
		});


		$(".btn-clear").click(function() {
			$(this).parent().parent().find('input').val('').submit();
		});


		//Logins: Apply ALL access level to all selects
		$('.access_apply_all').click(function() {
			name = 'sel_'+$(this).attr('id');
			sel = document.getElementsByName(name)[0];
			val = sel.options[sel.selectedIndex].value;
			$(this).closest('.panel-body').find('select.sel_rechte').each(function() {
				$(this).val(val);
			});
			return false;
		});


		$("input.textmultiplus-new").keypress(function(e) {
			if(e.keyCode == 13) {  //Return
				e.preventDefault();
				text = $(this).val();
				hid_name = $(this).attr('name').substr(4);
				name = 'sel_ds2_'+hid_name;
				double_select_add(text, text, name, hid_name);

				$(this).val('');

				e.cancelBubble;
				return false;
			}
		});


		/* Foreign table in forms */
		$("body").on('click', '.form_ft_new', function() {
			after = $(this).attr('data-after');
			field = $(this).attr('data-field');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,after,sesid', 'ftnew,'+field+','+pid+','+after+','+kOOL.sid, do_element);
		});

        $("body").on('click', 'button.form_ft_load_preset', function() {
            after = $(this).attr('data-after');
            field = $(this).attr('data-field');
            pid = $(this).attr('data-pid');
            preset_table = $(this).attr('data-preset-table');
            preset_join_value_local = $(this).attr('data-preset-join-value-local');
            preset_join_column_foreign = $(this).attr('data-preset-join-column-foreign');
            if (preset_join_value_local == null || preset_join_value_local == '') {
            }
            else {
                sendReq("/inc/ajax.php", 'action,field,pid,after,preset_table,join_value_local,join_column_foreign,sesid', 'ftloadpresets,'+field+','+pid+','+after+','+preset_table+','+preset_join_value_local+','+preset_join_column_foreign+','+kOOL.sid, do_element);
            }
            return false;
        });

		$("body").on('click', 'button.form_ft_save', function(event) {
			event.preventDefault();

			after = $(this).attr('data-after');
			field = $(this).attr('data-field');
			table = $(this).attr('data-table');
			pid = $(this).attr('data-pid');
			id = $(this).attr('data-id');
			if(!id) id = 0;

			action = id > 0 ? 'ftedit' : 'ftsave';

			//Put together formdata to be posted
			formData = new FormData();
			formData.append('action', action);
			formData.append('field', field);
			formData.append('id', id);
			formData.append('pid', pid);
			formData.append('after', after);
			formData.append('sesid', kOOL.sid);

			fields = $(this).attr('data-fields').split(',');
			var ckeditorNames = [];
			for(i=0; i<fields.length; i++) {
				el = document.getElementsByName('koi['+table+']['+fields[i]+']['+id+']')[0];
				if ($(el).hasClass('richtexteditor')) ckeditorNames.push(el.name);

				//Treat file uploads separately
				if(el.type == 'file') {
					files = el.files;
					if(files.length > 0) {
						formData.append('koi['+table+']['+fields[i]+']['+id+']', files[0], files[0].name);
					}
					//Check for delete checkbox
					elDel = document.getElementsByName('koi['+table+']['+fields[i]+'_DELETE]['+id+']')[0];
					if($(elDel).prop('checked')) {
						formData.append(fields[i]+'_DELETE', 1);
					}
				} else {
					var encodedStr = $(el).val().replace(/[\u2610\u2611]/gim, function(i) {return '&#'+i.charCodeAt(0)+';';});formData.append(fields[i], encodedStr);
				}
			}

			//Submit data as POST to include file uploads
			$.ajax({
				type: 'POST',
				url: '../inc/ajax.php',
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
				//Success: Fake functionality of do_element
				success: function(data) {
					// remove old ckeditor instances
					var nameId;
					for (nameId in ckeditorNames) {
						var editor = CKEDITOR.instances[ckeditorNames[nameId]];
						if (editor) { editor.destroy(true); }
					}

					split = data.split("@@@");
					el_id = split[0].trim();
					value = split[1];
					element = document.getElementsByName(el_id)[0];
					if(element) $(element).html(value);

					//Recall ckeditor for rte inputs
					$('.richtexteditor').ckeditor({customConfig : '/'+kOOL.module+'/inc/ckeditor_custom_config.js'});
				}
			});

		});


		$("body").on('change', 'input,select,textarea', function(event) {
			$(this).parents('div.form_ft_row').removeClass("list-group-item-info").addClass("list-group-item-danger");
		});

		$("body").on('click', 'button.form_ft_add', function(event) {
			after = $(this).attr('data-after');
			field = $(this).attr('data-field');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,after,sesid', 'ftnew,'+field+','+pid+','+after+','+kOOL.sid, do_element);
		});

		$("body").on('click', 'button.form_ft_delete', function(event) {
			field = $(this).attr('data-field');
			id = $(this).attr('data-id');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,id,sesid', 'ftdelete,'+field+','+pid+','+id+','+kOOL.sid, do_element);
		});

		$("body").on('click', 'button.form_ft_moveup', function(event) {
			field = $(this).attr('data-field');
			id = $(this).attr('data-id');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,id,direction,sesid', 'ftmove,'+field+','+pid+','+id+',up,'+kOOL.sid, do_element);
		});
		$("body").on('click', 'button.form_ft_movedown', function(event) {
			field = $(this).attr('data-field');
			id = $(this).attr('data-id');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,id,direction,sesid', 'ftmove,'+field+','+pid+','+id+',down,'+kOOL.sid, do_element);
		});


		$("body").on('change', ".sel-peoplefilter", function(event) {
			fid = $(this).data('value')+'';
			field = $(this).attr("name").substring(18);
			sendReq("/inc/ajax.php", 'action,field,fid,sesid', 'peoplefilterform,'+field+','+fid+','+kOOL.sid, do_element);
		});

		$("body").on('click', ".peoplefilter-submit", function(event) {
			fid = $(".sel-peoplefilter").data('value')+'';

			var1 = $("div.filter-form [name=var1]").val();
			if(typeof var1 === 'undefined') var1 = '';
			text1 = $("div.filter-form select[name=var1] option:selected").text();
			if(typeof text1 === 'undefined') text1 = '';

			var2 = $("div.filter-form [name=var2]").val();
			if(typeof var2 === 'undefined') var2 = '';
			text2 = $("div.filter-form select[name=var2] option:selected").text();
			if(typeof text2 === 'undefined') text2 = '';

			var3 = $("div.filter-form [name=var3]").val();
			if(typeof var3 === 'undefined') var3 = '';
			text3 = $("div.filter-form select[name=var3] option:selected").text();
			if(typeof text3 === 'undefined') text3 = '';

			neg = $("div.filter-form input[name=filter_negativ]").prop('checked') ? 1 : 0;

			//Add new option to select holding current filters
			newV = fid+'|'+var1+'|'+var2+'|'+var3+'|'+neg;
			text = $(".sel-peoplefilter").children('.'+doubleSelectActiveClass).text()+': ';
			if(neg) text += '!';

			if(text1) text += text1;
			else if(var1) text += var1;

			if(text2) text += ','+text2;
			else if(var2) text += ','+var2;

			if(text3) text += ','+text3;
			else if(var3) text += ','+var3;

			$(".peoplefilter-act").append(getSelectOption(newV, text));


			//Set hidden value which will be submitted
			var values = [];
			$(".peoplefilter-act").children().each(function() {
				var v = $(this).data('value')+'';
				if (v) values.push(v);
			});
			$("input.peoplefilter-value").val(values.join(','));

			return false;
		});


		$('body').on('click', "div.koi-checkboxes-entry label", function() {
			if($(this).children("input").prop("checked")) {
				$(this).parent().addClass("koi-checkboxes-checked");
			} else {
				$(this).parent().removeClass("koi-checkboxes-checked");
			}
			value = '';
			$(this).parent('div.koi-checkboxes-entry').parent("div.koi-checkboxes-container").find("input:checked").each(function() {
				value += (value != '' ? ',' : '') + $(this).val();
			});
			$(this).parent('div').parent("div.koi-checkboxes-container").children("input.koi-checkboxes-value").val(value);
		});


		$('body').on('click', '#ko_list_colitemlist_click[data-toggle="popover"]', function() {
			$('.popover-overlay.in').remove();
			$(this).popover({
				trigger: 'manual',
				html: 'true',
				container: 'body',
				placement: 'bottom'
			});
			$(this).popover('show');
			return false;
		});



		$('body').on('mouseenter', '.popover-overlay-trigger', function() {
			$('.popover-overlay.in').remove();
			$(this).popover('show');
		});

		$('body').on('mouseleave', '.popover-overlay-trigger', function(event) {
			if (!$(event.toElement).hasClass('arrow') && !$(event.relatedTarget).hasClass('arrow')) {
				$('.popover-overlay.in').remove();
			}
		});

		$('body').on('mouseleave', '.popover-overlay.in', function() {
			$(this).remove();
		});



		// ko-js-table
		$('body').on('click', '.ko-js-table-filter-item', function() {
			var $_this = $(this);

			var target = $_this.attr('data-filter-target');
			var col = $_this.attr('data-filter-col');
			if (!col || !target) return;

			if ($_this.hasClass('active')) {
				$_this.removeClass('active');
				$_this.removeClass('label-danger');
				$_this.addClass('label-default');
			}
			else {
				$_this.removeClass('label-default');
				$_this.addClass('label-danger');
				$_this.addClass('active');
			}

			ko_js_table_apply_filters(target);
		});

	});
}

// ko-js-table
function ko_js_table_apply_filters(target) {
	if (!target) return;

	ko_js_table_remove_form_rows(target);

	var filter_items = $('.ko-js-table-filter-item.active[data-filter-target="'+target+'"]');

	var query_strings = [''];
	var query_criteria = {};
	filter_items.each(function(index) {
		if (!query_criteria[$(this).attr('data-filter-col')]) query_criteria[$(this).attr('data-filter-col')] = [];
		query_criteria[$(this).attr('data-filter-col')].push([$(this).attr('data-filter-value'), $(this).attr('data-filter-match')]);
	});
	for (var data_col in query_criteria) {
		var col_criteria = query_criteria[data_col];
		var new_query_strings = [];
		for (var query_string_id in query_strings) {
			var query_string = query_strings[query_string_id];
			for (var data_value_id in col_criteria) {
				var data_value = col_criteria[data_value_id][0];
				var match_mode = col_criteria[data_value_id][1];
				var qs;
				if (match_mode == 'substring') {
					qs = '[data-col-'+data_col+'*="'+data_value+'"]';
				} else {
					qs = '[data-col-'+data_col+'="'+data_value+'"]';
				}
				new_query_strings.push(query_string+qs);
			}
		}
		query_strings = new_query_strings;
	}
	var query = '';
	for (var query_string_id in query_strings) {
		var query_string = query_strings[query_string_id];
		query = query + ',' + query_string;
	}
	if (query) query = query.substr(1);

	$(target).find('.ko-js-table-data-row').addClass('ko-js-table-data-row-filter-hidden');
	if (query) {
		$(target).find('.ko-js-table-data-row').filter(query).removeClass('ko-js-table-data-row-filter-hidden');
	}
	else {
		$(target).find('.ko-js-table-data-row').removeClass('ko-js-table-data-row-filter-hidden');
	}
}
function ko_js_table_remove_form_rows(target) {
	var elements = $(target).find('.ko-js-table-form-row');
	elements.each(function(key) {
		$(this).parent().children('.ko-js-table-data-row').removeClass('ko-js-table-data-row-form-hidden');
	});
	elements.remove();
}
function ko_js_table_hide_unnecessary_filters(target) {
	var filter_items = $('.ko-js-table-filter-item[data-filter-target="'+target+'"]');
	var data_rows = $(target).find('.ko-js-table-data-row');
	filter_items.each(function(e) {
		if (data_rows.filter('[data-col-'+$(this).attr('data-filter-col')+'="'+$(this).attr('data-filter-value')+'"]').length > 0) {
			$(this).show();
		} else {
			$(this).hide();
		}
	})
}


/**
 * checks mandatory fields, depicted by data-mandatory
 * @param selector
 */
function check_mandatory_fields(selector) {
	var nokFields = [];
	var okFields = [];
	var ok = true;
	$(selector).find('.mandatory').each(function() {
		var name = $(this).attr('name');
		if (name.substr(0, 3) == 'koi') name = name.substr(0, name.lastIndexOf("["));

		if ($(this).val() == '' || $(this).val() == null) {
			nokFields.push(name);
			ok = false;
		} else {
			okFields.push(name);
		}
	});

	$(selector).data('ko-validation-ok', ok);
	$(selector).data('ko-validation-ok-fields', okFields);
	$(selector).data('ko-validation-nok-fields', nokFields);
	$(selector).trigger('ko-validate');


	okFields = $(selector).data('ko-validation-ok-fields');
	nokFields = $(selector).data('ko-validation-nok-fields');

	var ok = true;
	nokFields.forEach(function(name) {
		ok = false;
		var $input = $('[name^="'+escapeSelector(name)+'"]');
		$input.closest('.formular-cell').children('.formular_header').addClass('form-danger');
	});

	okFields.forEach(function(name) {
		var $input = $('[name^="'+escapeSelector(name)+'"]');
		$input.closest('.formular-cell').children('.formular_header').removeClass('form-danger');
	});

	if (!ok) {
		alert(kOOL_ll['mandatory_field_missing']);
		$("html, body").animate({ scrollTop: 0 }, 200);
	}

	return ok;
}



/**
 * code to test function get_highlight_color(..)
 *
 function get_highlight_color(colorDecoded) {

        var highlightColor = 'cf510e';

		var highlightColorDecoded = [parseInt(highlightColor.substr(0,2),16), parseInt(highlightColor.substr(2,2),16), parseInt(highlightColor.substr(4,2),16)];

		var distance = Math.sqrt(Math.pow(colorDecoded[0] - highlightColorDecoded[0], 2) + Math.pow(colorDecoded[1] - highlightColorDecoded[1], 2) + Math.pow(colorDecoded[2] - highlightColorDecoded[2], 2));

		if (distance < 70) {
			highlightColor = '0e88c9';
		}

        		var highlightColorDecoded = [parseInt(highlightColor.substr(0,2),16), parseInt(highlightColor.substr(2,2),16), parseInt(highlightColor.substr(4,2),16)];

		return highlightColorDecoded;
	}



 $(function() {
	var stepSize = 25;
	var b = $('#color-table > tbody');
	for (var i = 0; i < 255; i = i + stepSize) {
		for (var j = 0; j < 255; j = j + stepSize) {
			for (var k = 0; k < 255; k = k + stepSize) {
				var color = [i, j, k];
				var highlightColor = get_highlight_color(color);
				b.append('<tr><td style="background-color: rgb('+color[0]+','+color[1]+','+color[2]+');"></td><td style="background-color: rgb('+highlightColor[0]+','+highlightColor[1]+','+highlightColor[2]+');"></td></tr>');
			}
		}
	}
})
 */
function get_highlight_color(color) {

	var highlightColor = 'cf510e';

	var colorDecoded = [parseInt(color.substr(0,2),16), parseInt(color.substr(2,2),16), parseInt(color.substr(4,2),16)];
	var highlightColorDecoded = [parseInt(highlightColor.substr(0,2),16), parseInt(highlightColor.substr(2,2),16), parseInt(highlightColor.substr(4,2),16)];

	var distance = Math.sqrt(Math.pow(colorDecoded[0] - highlightColorDecoded[0], 2) + Math.pow(colorDecoded[1] - highlightColorDecoded[1], 2) + Math.pow(colorDecoded[2] - highlightColorDecoded[2], 2));

	if (distance < 50) {
		highlightColor = 'ffffff';
	}

	return highlightColor;
}





/* Submit changes */
function inlineform_submit(obj, fullid) {
    //Store changes and redraw table cell
    submit_cols = new Array("action", "id", "module", "sesid");
    submit_values = new Array("inlineformsubmit", fullid, kOOL.module, kOOL.sid);
    c = 4;
    $(obj).closest(".inlineform").find(
		"input[name^=koi][type=text], " +
		"input[name^=koi][type=hidden], " +
		"input[name^=koi][type=color], " +
		"input[name^=koi][type=date], " +
		"input[name^=koi][type=datetime], " +
		"input[name^=koi][type=datetime-local], " +
		"input[name^=koi][type=email], " +
		"input[name^=koi][type=month], " +
		"input[name^=koi][type=number], " +
		"input[name^=koi][type=range], " +
		"input[name^=koi][type=search], " +
		"input[name^=koi][type=tel], " +
		"input[name^=koi][type=time], " +
		"input[name^=koi][type=url], " +
		"input[name^=koi][type=week], " +
		"textarea[name^=koi]"
	).each(function() {
        submit_cols[c] = $(this).attr("name");
        submit_values[c] = encodeURIComponent($(this).val().replace(new RegExp(",", "g"), '|').replace(new RegExp('\n', "g"), '<br />'));
        c++;
    });
    $(obj).closest(".inlineform").find("select[name^=koi]").each(function() {
        //alert($(obj).attr("name"));
        submit_cols[c] = $(this).attr("name");
        submit_values[c] = $(this).val().replace(new RegExp(",", "g"), '|');
        c++;
    });
    //alert(submit_cols.join(","));
    //alert(submit_values.join(","));
    var params = {};
    for(i=0; i<submit_cols.length; i++) {
        params[submit_cols[i]] = submit_values[i];
    }

    $.get("../inc/ajax.php", params, function(data) {
        responseText = data;

        //get element id and values
        split = responseText.split("@@@");
        k = 0;
        while (split[k]) {
            el_id = split[k].trim();
            value = split[k+1];

            if(el_id == "ERROR") {
                ko_infobox(el_id, value);
            }
            else if(el_id != "") {
                element = document.getElementById(el_id);
                if(element) {
                    //Find JavaScript to be called (e.g. for input jscalendar)
                    js_code = new Array();
                    while(value.indexOf('<script type="text/javascript">') > -1) {
                        start = value.indexOf('<script type="text/javascript">');
                        stop = value.indexOf('</script>')+9;
                        //Store JS code to be executed later
                        js_code.push(value.substring(start, stop).replace(new RegExp('<script type="text/javascript">'), '').replace(new RegExp('</script>'), ''));
                        //Delete JS code from html code
                        value = value.substring(0, start) + value.substring(stop);
                    }

                    element.innerHTML = value;

                    //Set focus
                    if_element = document.getElementById('if_'+el_id);
                    if(if_element) {
                        //Add class if-noblur if more than one input element
                        if($(if_element).find("input, textarea, select").length > 1 || $(if_element).find("input.jsdate-input").length > 0) {
                            $(if_element).find("input, textarea, select").addClass("if-noblur");
                        }
                        //Set focus to input element
                        $(if_element).find("input, textarea, select").first().focus();
                    }

                    //Execute JS code after HTML has been outputted
                    if(js_code.length > 0) {
                        for(i=0; i<js_code.length; i++) {
                            eval(js_code[i]);
                        }
                    }

                }
            }
            k = k + 2;
        }
    })
        .fail(function(e) {
            console.log(e)
        })
}//inlineform_submit()


function inlineform_show() {
	if(http.readyState == 4) {
		if(http.status == 200) {
			responseText = http.responseText;

			//get element id and values
			split = responseText.split("@@@");
            k = 0;
            while (split[k]) {
                el_id = split[k].trim();
                value = split[k+1];

                if(el_id == "ERROR") {
                    ko_infobox(el_id, value);
                }
                else if(el_id != "") {
                    element = document.getElementById(el_id);
                    if(element) {
                        //Find JavaScript to be called (e.g. for input jscalendar)
                        js_code = new Array();
                        while(value.indexOf('<script type="text/javascript">') > -1) {
                            start = value.indexOf('<script type="text/javascript">');
                            stop = value.indexOf('</script>')+9;
                            //Store JS code to be executed later
                            js_code.push(value.substring(start, stop).replace(new RegExp('<script type="text/javascript">'), '').replace(new RegExp('</script>'), ''));
                            //Delete JS code from html code
                            value = value.substring(0, start) + value.substring(stop);
                        }

                        element.innerHTML = value;

                        //Set focus
                        if_element = document.getElementById('if_'+el_id);
                        if(if_element) {
                            //Add class if-noblur if more than one input element
                            if($(if_element).find("input, textarea, select").length > 1 || $(if_element).find("input.jsdate-input").length > 0) {
                                $(if_element).find("input, textarea, select").addClass("if-noblur");
                            }
                            //Set focus to input element
                            $(if_element).find("input, textarea, select").first().focus();
                        }

                        //Execute JS code after HTML has been outputted
                        if(js_code.length > 0) {
                            for(i=0; i<js_code.length; i++) {
                                eval(js_code[i]);
                            }
                        }

                    }
                }
                k = k + 2;
            }


		}//if(http.status == 200)
		else if (http.status == 404) {
			alert("Request URL does not exist");
		}

		//Hide ajax box
		msg = document.getElementsByName('wait_message')[0];
		msg.style.display = "none";
		document.body.style.cursor = 'default';
  }
}//inlineform_show()




function kota_show_filter(table, col) {
}//kota_show_filter()


var peoplesearchTimer;

$(document).ready(function() {

	// show filter on right click on a column heading [ko_list]
	$('body').on('contextmenu', 'th.ko_listh_sorting[data-toggle="popover"]', function(event) {
		$(this).click();
		event.preventDefault();
		return false;
	});
	$('body').on('click', 'th.ko_listh_sorting[data-toggle="popover"]', function(event) {
		$('.popover[role="tooltip"].in').remove();
		$(this).popover({
			trigger: 'manual',
			html: 'true',
			container: '#main_content'
		});
		$(this).popover('show');
		event.preventDefault();
		event.stopPropagation();
		return false;
	});

	// show filter on right click on a column heading [ko_list2]
	$('body').on('contextmenu', 'th.ko_listh_filter[data-toggle="popover"]', function(event) {
		$(this).click();
		return false;
	});
	$('body').on('click', 'th.ko_listh_filter[data-toggle="popover"]', function(event) {
		$('.popover[role="tooltip"].in').remove();
		$(this).popover({
			trigger: 'manual',
			html: 'true',
			container: '#main_content',
			placement: 'bottom'
		});

		var filterEnabled = $(this).attr('data-filter-enabled');
		var table = $(this).attr('data-table');
		var sortEnabled = $(this).attr('data-sort-enabled');
		var sortAction = $(this).attr('data-sort-action');
		var sortOrder = $(this).parent().parent().parent().attr('data-sort-order');
		var sortCol = $(this).parent().parent().parent().attr('data-sort-col');

		var target = $(this);
		var params = {
			action: 'kotafilter',
			module: kOOL.module,
			table: table,
			sesid: kOOL.sid
		};
		if (sortEnabled != 'true') {
			params['sortenabled'] = '0';
		}
		else {
			params['sortenabled'] = '1';
			params['sortcol'] = sortCol;
			params['sortorder'] = sortOrder;
			params['sortaction'] = sortAction;
			params['sortby'] = target.attr('data-sort-by');
		}
		if (filterEnabled != 'true') {
			params['filterenabled'] = '0';
		}
		else {
			var all = $(this).attr('id').substring(6);
			var split = all.split(':');
			var cols = split[1].trim();
			params['filterenabled'] = '1';
			params['cols'] = cols;
		}
		$.get("../inc/ajax.php", params, function(data) {
			if(data != '') {
				$('.popover[role="tooltip"].in').remove();
				var dataParts = data.split('@@@');
				target.attr('data-original-title', dataParts[0]);
				target.attr('data-content', dataParts[1]);
				target.popover('show');
			}
		});
		event.preventDefault();
		event.stopPropagation();
		return false;
	});
	$('body').on("click", '.popover[role="tooltip"]', function(e) {
		e.stopPropagation();
	});


	//Filter submission
	$('body').on("keypress", ".kota_filter_inputs", function(e) {
		if (e.which == 13) $("#kota_filterbox_submit").click();
	});
	$('body').on("click", "#kota_filterbox_submit", function(e) {
		e.preventDefault();

		//Collect all filter inputs and submit them
		submit_cols = new Array("action", "module", "sesid");
		submit_values = new Array("kotafiltersubmit", kOOL.module, kOOL.sid);
		c = 3;

		//Negative checkbox
		submit_cols[c] = "neg";
		submit_values[c] = $("#kota_filterbox_neg").prop("checked") ? 1 : 0;
		c++;

		$(".kota_filter_inputs").each(function() {
			submit_cols[c] = $(this).attr("name");
			submit_values[c] = $(this).val().replace(new RegExp(",", "g"), '|');
			if ($(this).attr('type') == 'checkbox') {
				if (!$(this).prop('checked')) {
					submit_values[c] = '0';
				}
			}
			c++;
		});
		//Submit and redraw list
		sendReq("../inc/ajax.php", submit_cols, submit_values, do_element);

	});

	//Clear this filters
	$('body').on("click", "#kota_filterbox_clear", function(e) {
		e.preventDefault();

		sendReq("../inc/ajax.php", "action,module,sesid,id", "kotafilterclear,"+kOOL.module+","+kOOL.sid+","+$(this).attr("rel").replace(new RegExp(",", "g"), '|'), do_element);
	});


	//people search
	// Text changed in search input
	$(".peoplesearch").keyup(function(e) {
		if(e.keyCode == 40) {  //Down arrow
			$(this).parent(".peoplesearchwrap").find("select").focus();
			$(this).parent(".peoplesearchwrap").find("select option:first-child").attr("selected", "selected");
		} else {
			var caller = this;
			clearTimeout(peoplesearchTimer);
			peoplesearchTimer = setTimeout(function() {
				token = $(caller).attr('data-source');
				$.get("../leute/inc/ajax.php", {action: "peoplesearch", string: $(caller).val(), name: $(caller).attr("name"), token: token, sesid: kOOL.sid}, function(data) {
					$(caller).parent(".peoplesearchwrap").find("select.peoplesearchresult").html(data);
					$(caller).parent(".peoplesearchwrap").find(".peoplesearchresult").show();
				});
			}, 200);
		}
	});
	// Move up to input from result select if up arrow is hit on top element
	$(".peoplesearchresult").keypress(function(e) {
		if($(this).find("option:first-child").attr("selected") == "selected" && e.keyCode == 38) {  //Up Arrow
			$(this).parent(".peoplesearchwrap").find("input.peoplesearch").focus();
		}
	});
	// Return key on result entry assigns this entry and refocuses the input element
	$("select.peoplesearchresult").keypress(function(e) {
		if(e.keyCode == 13) {  //Return
			e.preventDefault();

			name = $(this).attr("name").slice(8);
			value = $(this).children(":selected").val();
			label = $(this).children(":selected").attr("label");
			double_select_add(label, value, 'sel_ds2_'+name, name);

			$(this).parent(".peoplesearchwrap").find("input.peoplesearch").focus();
		}
	});
	// Clicking a result entry selects this
	$("select.peoplesearchresult").click(function(e) {
		name = $(this).attr("name").slice(8);
		//this.children("[selected]") from above doesn't seem to work here if only one option is present in the select...
		if(this.selectedIndex >= 0) {
			value = this.options[this.selectedIndex].value;
			label = this.options[this.selectedIndex].text;
			double_select_add(label, value, 'sel_ds2_'+name, name);
		}
	});


	//Table sort
	$("body").on("sortupdate", "table.ko_list.sortable tbody", function(event, ui) {
		diff = Math.round((ui.position.top - ui.originalPosition.top) / ui.item.height());
		id = ui.item.attr("data-id");
		table = ui.item.closest("table.ko_list.sortable").attr("data-table");
		sendReq("../inc/ajax.php", "action,table,module,id,diff,sesid", "tablesort,"+table+","+kOOL.module+","+id+","+diff+","+kOOL.sid, do_element);
	});


	// searchbox
	$('body').on('keypress', '#general-search-container', function(e) {
		if (e.which == 13) {
			var value = $(this).find('input').val();
			sendReq('inc/ajax.php', ['sesid','action','value'], [kOOL.sid,'submitgeneralsearch',value], do_element);
			return false;
		}
	});
	$('body').on('click', '#submit-search-btn', function() {

	});
	$('body').on('click', '#clear-search-btn', function() {

	});
	$('body').on('keypress', '#searchbox-inputs', function(e) {
		if (e.which == 13) $('#submit-search-btn').click();
	});

	$('body').on('click', '.textplus-list li', function(e) {
		var $this = $(this);
		var $group = $this.closest('.textplus-wrapper');
		var $display = $group.find('.textplus-display');

		$display.val($this.text());
		$display.focus();

		e.preventDefault();
	});
});




function ko_infobox(mode, text) {
	TINY.box.show({html:text,animate:false,close:false,mask:false,height:'auto',boxid:'ko_infobox_'+mode,autohide:5,top:0});
}//ko_infobox()



function popup(d, w, h) {
	ko_popup(d, w, h);
}


function ko_popup(d, w, h) {
	w = w ? w : 350;
	h = h ? h : 0;
	TINY.box.show({url:d,animate:true,close:true,mask:true,width:w,height:h});
}//ko_popup()


function ko_image_popup(d) {
	TINY.box.show({image:d,animate:true,close:true,mask:true});
}//ko_popup()



function textarea_insert_text(textid, _inserttext) {
	el = document.getElementById(textid);

	inserttext = unescape(_inserttext);

	//Keep scroll position
	textAreaScrollPosition = el.scrollTop;

	//IE support
	if(document.selection) {
		el.focus();

		//in effect we are creating a text range with zero
		//length at the cursor location and replacing it
		//with inserttext
		sel = document.selection.createRange();
		sel.text = inserttext;

	}
	//Mozilla/Firefox/Netscape 7+ support
	else if(el.selectionStart || el.selectionStart == '0') {

		el.focus();
		//Here we get the start and end points of the
		//selection. Then we create substrings up to the
		//start of the selection and from the end point
		//of the selection to the end of the field value.
		//Then we concatenate the first substring, inserttext,
		//and the second substring to get the new value.
		var startPos = el.selectionStart;
		var endPos = el.selectionEnd;
		el.value = el.value.substring(0, startPos) + inserttext + el.value.substring(endPos, el.value.length);
		el.setSelectionRange(endPos+inserttext.length, endPos+inserttext.length);
	} else {
		el.value += inserttext;
	}

	//Restore scroll position
	el.scrollTop = textAreaScrollPosition;
}

function richtexteditor_insert_text(name, _inserttext) {
    for(var i in CKEDITOR.instances) {
        if (CKEDITOR.instances[i].name == name) {
            CKEDITOR.instances[i].insertText(decodeURIComponent(_inserttext));
        }
    }
}

function richtexteditor_insert_html(name, _inserthtml) {
    for(var i in CKEDITOR.instances) {
        if (CKEDITOR.instances[i].name == name) {
            CKEDITOR.instances[i].insertHtml(decodeURIComponent(_inserthtml));
        }
    }
}




function do_fill_grouproles_select_filter() {
	if(http.readyState == 4) {
		if(http.status == 200) {
			responseText = http.responseText;

			//Select leeren
			list = document.getElementsByName('var2')[0];
			for (var i=list.options.length-1; i>=0; i--) {
				list.options[i]=null;
			}

			//Optionen splitten und einfï¿½llen
			var options = responseText.split("#");
			for(i=0; i<options.length; i++) {
				temp = options[i].split(",");
				list.options[i] = new Option(temp[1], temp[0]);
			}

		}//if(http.status == 200)
		else if (http.status == 404)
			alert("Request URL does not exist");

		//Hide message box
		msg = document.getElementsByName('wait_message')[0];
		msg.style.display = "none";
		document.body.style.cursor = 'default';
  }
}//do_fill_groupsroles_select()





TINY = {};
TINY.box=function(){
	var j,m,b,g,v,p=0;
	return{
		show:function(o){
			v={opacity:70,close:1,animate:1,fixed:1,mask:1,maskid:'',boxid:'',topsplit:2,url:0,post:0,height:0,width:0,html:0,iframe:0};
			for(s in o){v[s]=o[s]}
			if(!p){
				j=document.createElement('div'); j.className='tbox';
				p=document.createElement('div'); p.className='tinner';
				b=document.createElement('div'); b.className='tcontent';
				m=document.createElement('div'); m.className='tmask';
				g=document.createElement('div'); g.className='tclose'; g.v=0;
				$(g).html('<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>');
				document.body.appendChild(m); document.body.appendChild(j); j.appendChild(p); p.appendChild(b);
				m.onclick=g.onclick=TINY.box.hide; window.onresize=TINY.box.resize
			}else{
				j.style.display='none'; clearTimeout(p.ah); if(g.v){p.removeChild(g); g.v=0}
			}
			p.id=v.boxid; m.id=v.maskid; j.style.position=v.fixed?'fixed':'absolute';
			if(v.html&&!v.animate){
				p.style.backgroundImage='none'; b.innerHTML=v.html; b.style.display='';
				p.style.width=v.width?v.width+'px':'auto'; p.style.height=v.height?v.height+'px':'auto'
			}else{
				b.style.display='none'; 
				if(!v.animate&&v.width&&v.height){
					p.style.width=v.width+'px'; p.style.height=v.height+'px'
				}else{
					p.style.width=p.style.height='100px'
				}
			}
			if(v.mask){this.mask(); this.alpha(m,1,v.opacity)}else{this.alpha(j,1,100)}
			if(v.autohide){p.ah=setTimeout(TINY.box.hide,1000*v.autohide)}else{document.onkeyup=TINY.box.esc}
		},
		fill:function(c,u,k,a,w,h){
			if(u){
				if(v.image){
					var i=new Image(); i.onload=function(){w=w||i.width; h=h||i.height; TINY.box.psh(i,a,w,h)}; i.src=v.image
				}else if(v.iframe){
					this.psh('<iframe src="'+v.iframe+'" width="'+v.width+'" frameborder="0" height="'+v.height+'"></iframe>',a,w,h)
				}else{
					var x=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject('Microsoft.XMLHTTP');
					x.onreadystatechange=function(){
						if(x.readyState==4&&x.status==200){p.style.backgroundImage=''; TINY.box.psh(x.responseText,a,w,h)}
					};
					if(k){
    	            	x.open('POST',c,true); x.setRequestHeader('Content-type','application/x-www-form-urlencoded'); x.send(k)
					}else{
       	         		x.open('GET',c,true); x.send(null)
					}
				}
			}else{
				this.psh(c,a,w,h)
			}
		},
		psh:function(c,a,w,h){
			if(typeof c=='object'){b.appendChild(c)}else{b.innerHTML=c}
			var x=p.style.width, y=p.style.height;
			if(!w||!h){
				p.style.width=w?w+'px':''; p.style.height=h?h+'px':''; b.style.display='';
				if(!h){h=parseInt(b.offsetHeight)}
				if(!w){w=parseInt(b.offsetWidth)}
				b.style.display='none'
			}
			p.style.width=x; p.style.height=y;
			this.size(w,h,a)
		},
		esc:function(e){e=e||window.event; if(e.keyCode==27){TINY.box.hide()}},
		hide:function(){TINY.box.alpha(j,-1,0,3); document.onkeypress=null; if(v.closejs){v.closejs()}},
		resize:function(){TINY.box.pos(); TINY.box.mask()},
		mask:function(){m.style.height=this.total(1)+'px'; m.style.width=this.total(0)+'px'},
		pos:function(){
			var t;
			if(typeof v.top!='undefined'){t=v.top}else{t=(this.height()/v.topsplit)-(j.offsetHeight/2); t=t<20?20:t}
			if(!v.fixed&&!v.top){t+=this.top()}
			j.style.top=t+'px'; 
			j.style.left=typeof v.left!='undefined'?v.left+'px':(this.width()/2)-(j.offsetWidth/2)+'px'
		},
		alpha:function(e,d,a){
			clearInterval(e.ai);
			if(d){e.style.opacity=0; e.style.filter='alpha(opacity=0)'; e.style.display='block'; TINY.box.pos()}
			e.ai=setInterval(function(){TINY.box.ta(e,a,d)},20)
		},
		ta:function(e,a,d){
			var o=Math.round(e.style.opacity*100);
			if(o==a){
				clearInterval(e.ai);
				if(d==-1){
					e.style.display='none';
					e==j?TINY.box.alpha(m,-1,0,2):b.innerHTML=p.style.backgroundImage=''
				}else{
					if(e==m){
						this.alpha(j,1,100)
					}else{
						j.style.filter='';
						TINY.box.fill(v.html||v.url,v.url||v.iframe||v.image,v.post,v.animate,v.width,v.height)
					}
				}
			}else{
				var n=a-Math.floor(Math.abs(a-o)*.5)*d;
				e.style.opacity=n/100; e.style.filter='alpha(opacity='+n+')'
			}
		},
		size:function(w,h,a){
			if(a){
				clearInterval(p.si); var wd=parseInt(p.style.width)>w?-1:1, hd=parseInt(p.style.height)>h?-1:1;
				p.si=setInterval(function(){TINY.box.ts(w,wd,h,hd)},20)
			}else{
				p.style.backgroundImage='none'; if(v.close){p.appendChild(g); g.v=1}
				p.style.width=w+'px'; p.style.height=h+'px'; b.style.display=''; this.pos();
				if(v.openjs){v.openjs()}
			}
		},
		ts:function(w,wd,h,hd){
			var cw=parseInt(p.style.width), ch=parseInt(p.style.height);
			if(cw==w&&ch==h){
				clearInterval(p.si); p.style.backgroundImage='none'; b.style.display='block'; if(v.close){p.appendChild(g); g.v=1}
				if(v.openjs){v.openjs()}
			}else{
				if(cw!=w){p.style.width=(w-Math.floor(Math.abs(w-cw)*.6)*wd)+'px'}
				if(ch!=h){p.style.height=(h-Math.floor(Math.abs(h-ch)*.6)*hd)+'px'}
				this.pos()
			}
		},
		top:function(){return document.documentElement.scrollTop||document.body.scrollTop},
		width:function(){return self.innerWidth||document.documentElement.clientWidth||document.body.clientWidth},
		height:function(){return self.innerHeight||document.documentElement.clientHeight||document.body.clientHeight},
		total:function(d){
			var b=document.body, e=document.documentElement;
			return d?Math.max(Math.max(b.scrollHeight,e.scrollHeight),Math.max(b.clientHeight,e.clientHeight)):
			Math.max(Math.max(b.scrollWidth,e.scrollWidth),Math.max(b.clientWidth,e.clientWidth))
		}
	}
}();




/**************     DOUBLESELECT AND DYNSELECT    ***************/
var doubleSelectActiveClass;
$(function() {
	doubleSelectActiveClass = 'active';
	$('body').on('click', '.select-item', function(e) {
		var $this = $(this);
		var single = $this.data('select') == 'single';
		var $parent = $this.parent();
		var wasOn = $this.hasClass(doubleSelectActiveClass);
		var $children = $parent.children();

		var oldVal = $parent.data('value');
		if (typeof(oldVal) != "undefined") oldVal = oldVal + '';

		var $oldActive = $children.filter('.'+doubleSelectActiveClass);
		var $oldInactive = $children.not('.'+doubleSelectActiveClass);

		if (e.metaKey && !single) {
			$this.toggleClass(doubleSelectActiveClass);
		} else if (e.shiftKey && !single) {
			var lastClicked = $parent.data('lastClicked');
			if (lastClicked == this) return;
			if (lastClicked) {
				var start = $children.index(this);
				var end = $children.index(lastClicked);
				$children.slice(Math.min(start, end), Math.max(start, end) + 1)
					.toggleClass(doubleSelectActiveClass, ($(lastClicked).hasClass(doubleSelectActiveClass) || $parent.hasClass('doubleselect-left')));
			} else {
				$this.toggleClass(doubleSelectActiveClass);
			}
		} else {
			$children.removeClass(doubleSelectActiveClass);
			$this.toggleClass(doubleSelectActiveClass);
		}

		var newVals = [];
		$parent.children('.'+doubleSelectActiveClass).each(function() {
			var v = $(this).data('value')+'';
			if (v) newVals.push(v);
		});
		var newVal = newVals.join(',');

		if (newVal != oldVal) {
			$parent.trigger('before-change');

			var jsFuncBeforeChange = $this.parent().data('js-func-before-change');
			if (jsFuncBeforeChange) {
				var cont = window[jsFuncBeforeChange](newVal, oldVal);
				if (cont === false) {
					$oldActive.addClass(doubleSelectActiveClass);
					$oldInactive.removeClass(doubleSelectActiveClass);
					return false;
				}
			}
		}

		$this.parent().data('lastClicked', this);
		$parent.data('value', newVal);

		if (newVal != oldVal) $parent.trigger('change');
	});
	$('body').on('click', '.doubleselect-left', function () {
		var $this = $(this);
		var jsFuncAdd = $this.data('js-func-add');
		var targetName = $this.data('target-name');
		var hiddenName = $this.data('hidden-name');
		var $activeOptions = $this.find('.select-item.'+doubleSelectActiveClass);
		$activeOptions.each(function() {
			window[jsFuncAdd](
				$(this).text()+"",
				$(this).data('value')+"",
				targetName,
				hiddenName
			);
		});
		var afterFuncAdd = $this.data('js-after-add');
		if (afterFuncAdd) eval(afterFuncAdd);
		$activeOptions.removeClass(doubleSelectActiveClass);
	});
	$('body').on('keypress', '.doubleselect-left', function (e) {
		var $this = $(this);
		switch (e.which) {
			case 13:
				$this.click();
				break;
			default:
				return false;
		}
	});
	$('body').on('click', '.dynselect', function () {
		var $this = $(this);
		var jsFuncAdd = $this.data('js-func-add');
		var name = $this.attr('name');
		var $hidden = $(document.getElementsByName($this.data('hidden-name'))[0]);
		var $activeOption = $this.find('.select-item.'+doubleSelectActiveClass);
		if ($activeOption.length == 0) return false;
		$activeOption = $($activeOption[0]);

		var value = $activeOption.data('value')+'';

		if (value.substr(0,1) != 'i') $hidden.val(value);
		else $hidden.val('');
		var ok = window[jsFuncAdd](
			$activeOption.text()+"",
			$activeOption.data('value')+"",
			name
		);

		$this.updateActiveChildren();
	});
	$('body').on('click', '.groupselect-left', function() {
		if(!checkList(1)) return false;
		var $this = $(this);
		var jsFuncAdd = $this.data('js-func-add');
		var value = $this.data('value')+'';
		value.split(',').forEach(function(v) {
			if (v) window[jsFuncAdd](v);
		});
	});
	$('body').on('click', '.groupfilter', function() {
		if(!checkList(1)) return false;
		var $this = $(this);
		var $hidden = $('[name="var1"]');
		$hidden.val($this.data('value')+'');
		sendReq('../groups/inc/ajax.php', ['action','group_id'], ['grouproleselectfilter',$this.data('value')+''], do_fill_grouproles_select_filter);
	});
	$('body').on('click', '.dyndoubleselect', function() {
		var $this = $(this);
		if ($this.data('nocheck-list')) {
			if (!checkList(1)) return false;
		}
		var targetName = $this.data('target-name');
		var hiddenName = $this.data('hidden-name');
		var $activeOption = $this.find('.select-item.'+doubleSelectActiveClass);
		if ($activeOption.length == 0) return false;
		$activeOption = $($activeOption[0]);
		double_select_add($activeOption.text()+'', $this.data('value')+'', targetName, hiddenName);
	});
	$('body').on('click', '.ko-select-wrapper', function() {
		var $parentEl = $(this).find('.dyndoubleselect, .dynselect, .doubleselect-left');
		var hiddenName = $parentEl.data('hidden-name');
		var $hidden = $(document.getElementsByName(hiddenName)[0]);
		var oldVal = $hidden.data('ko-select-old-val');
		if (typeof(oldVal) == 'undefined' || oldVal != $hidden.val()) {
			$hidden.data('ko-select-old-val', $hidden.val());
			$hidden.trigger('change');
		}
	});

	// Used for charts
	$('body').on('click', '.fullscreen-btn', function() {
		var $this = $(this);
		if ($this.hasClass('is-fullscreen')) {
			$this.removeClass('is-fullscreen');
			exitFullScreen($($this.data('target')+'')[0]);
		} else {
			$this.addClass('is-fullscreen');
			enterFullScreen($($this.data('target')+'')[0]);
		}
	});

	$('body').on('click', '.google-charts-download-btn', function() {
		var $target = $($(this).data('target')+'');
		exitFullScreen($target[0]);
	});
});
function getSelectOption(value, text, title) {
	if (typeof(title) == "undefined" || title == null) title = text;
	return '<div class="select-item" data-value="'+value+'" title="'+title+'">'+text+'</div>';
}


function escapeSelector(selector) {
	return selector.replace(/(:|\.|\[|\]|,)/g, "\\$1");
}


$.fn.setVal = function(value) {
	if (this.hasClass('switch')) this.bootstrapSwitch('state', value && value != '0' ? 1 : 0);
	else if (this.hasClass('richtexteditor')) CKEDITOR.instances[this.attr('name')].setData(value);
	else if (this.hasClass('doubleselect')) {
		var $target = $('[name="'+escapeSelector(this.data('target-name'))+'"]');
		var $hidden = $('[name="'+escapeSelector(this.data('hidden-name'))+'"]');
		var html = ''; var hiddenValue = [];
		$.each(value, function (i, value) {
			html += getSelectOption(value.value, value.text, value.title);
			hiddenValue.push(value.value);
		});
		$hidden.val(hiddenValue.join(','));
		$target.html(html);
	}
	else if (this.hasClass('switch')) this.bootstrapSwitch('state', value && value != '0' ? 1 : 0);
	else (this.val(value));
};

/*$.fn.getVal = function() {
	if (this.hasClass('switch')) return this.bootstrapSwitch('state') ? 1 : 0;
	//else if (this.hasClass('richtexteditor')) return CKEDITOR.instances[this.attr('name')] ? CKEDITOR.instances[this.attr('name')].getData() : '';
	else if (this.hasClass('doubleselect')) return $('[name="'+escapeSelector(this.data('hidden-name'))+'"]').val();
	else if (this.prop('tagName').toLowerCase() == 'select' && this.prop('multiple')) return this.val() ? this.val().join(',') : '';
	else return this.val();
};*/

$.fn.toAssocArray = function() {
	var obj = {};
	this.find(':input').each(function() {
		obj[$(this).attr('name')] = $(this).val();
	});
	return obj;
};


$.fn.updateActiveChildren = function() {
	var $this = $(this);
	var value = $this.data('value')+"";
	var values = value.split(',');

	$this.children('.select-item').each(function() {
		if (values.indexOf($(this).data('value')+"") >= 0) {
			$(this).addClass(doubleSelectActiveClass);
		} else {
			$(this).removeClass(doubleSelectActiveClass);
		}
	});
};




function enterFullScreen(elem) {
	var reqFullScreenFcn = elem.requestFullscreen || elem.requestFullScreen || elem.mozRequestFullScreen || elem.webkitRequestFullscreen || elem.msRequestFullscreen;
	if(reqFullScreenFcn) {
		$(elem).addClass('is-fullscreen').trigger('fullscreen.entering');
		reqFullScreenFcn.call(elem);
	}
}
var exitFullScreenFcn = document.exitFullscreen || document.exitFullScreen || document.mozCancelFullScreen || document.webkitExitFullscreen || document.msExitFullscreen;
function exitFullScreen(elem) {
	if (exitFullScreenFcn) {
		$(elem).trigger('fullscreen.exiting');
		exitFullScreenFcn.call(document);
	}
}
$(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange', function() {
	fullscreenElement = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement;
	if(!fullscreenElement){
		$('.fullscreen-elem.is-fullscreen').removeClass('is-fullscreen').trigger('fullscreen.exited');
		$('.fullscreen-btn.is-fullscreen').removeClass('is-fullscreen');
	} else {
		$(fullscreenElement).trigger('fullscreen.entered');
	}
});


function initGoogleDownloadBtn ($chart, $btn) {
	$btn.attr('href', $chart.data('google.chart').getImageURI().replace('image/png', 'image/octet-stream'));
	var title = 'chart';
	$btn.attr('download', title+'.png');
}