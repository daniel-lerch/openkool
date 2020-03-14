//Navigation
sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

smHover = function() {
	try {
		var smEls = document.getElementById("sm").getElementsByTagName("LI");
		for (var i=0; i<smEls.length; i++) {
			smEls[i].onmouseover=function() {
				this.className+=" smhover";
			}
			smEls[i].onmouseout=function() {
				this.className=this.className.replace(new RegExp(" smhover\\b"), "");
			}
		}
	} catch(e) {
	}
}
if (window.attachEvent) window.attachEvent("onload", smHover);



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
	var list = document.getElementsByName(name)[0];
	//Keine doppelten Einträge erlauben
	for(var i=0; i<list.length; i++) {
		if(list.options[i].value == value) return;
	}
	//Neues Element hinzufügen
	var neu = new Option(text, value);
	list.options[list.length] = neu;

	//Hidden-Value füllen
	var hid = document.getElementsByName(hid_name)[0];
	var res = document.getElementsByName(name)[0];
	hid.value = "";
	for(var i=0; i<res.length; i++) {
		if(res.options[i].value != "") hid.value += res.options[i].value+",";
	}
	if(hid.value != "") hid.value = hid.value.slice(0, -1);
}//double_select_add()


function double_select_move(name, mode) {
	var S=document.getElementsByName(('sel_ds2_'+name))[0];

		//delete
	if(mode == "del") {
		S.remove(S.selectedIndex);
	} else {
		//move modes
		if(mode == "top") x = -S.selectedIndex;
		if(mode == "up") x = -1;
		if(mode == "down") x = 1;
		if(mode == "bottom") x = S.options.length-S.selectedIndex-1;

		var I=(sI=S.selectedIndex)+(x);
		if(I>=S.options.length || I<0) return;

		var myOption=S.options[sI];
		S.remove(sI);
		//readd selected entry
		if(navigator.userAgent.indexOf('MSIE')!=-1) {
			S.add(myOption,I);
		} else {
			S.add(myOption,S.options[I]);
		}
		S.selectedIndex = I;
	}

	//update hidden value
	var hid = document.getElementsByName(name)[0];
	hid.value = "";
	for(var i=0; i<S.length; i++) {
		if(S.options[i].value != "") hid.value += S.options[i].value+",";
	}
	if(hid.value != "") hid.value = hid.value.slice(0, -1);
}//double_select_move()



function do_fill_select() {
	if(http.readyState == 4) {
		if (http.status == 200) {
			responseText = http.responseText;

			//get element id and values
			split = responseText.split("@@@");
			el_id = split[0].trim();
			value = split[1];

			//Select leeren
			list = document.getElementsByName(el_id)[0];
			if(list) {
				for (var i=list.options.length-1; i>=0; i--) {
					list.options[i]=null;
				}

				//Optionen splitten und einfüllen
				var options = value.split("#");
				for(i=0; i<options.length; i++) {
					temp = options[i].split(",");
					list.options[i] = new Option(temp[1], temp[0]);
					if(temp[2]) list.options[i].title = temp[2];
				}
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



function forAllHeader(obj_id, chk_id) {
  obj = document.getElementById(obj_id);
  chk = document.getElementById(chk_id);
  if(obj.style.visibility == "hidden") {
		state = "open";
    obj.style.visibility = "visible";
    obj.style.display = "block";
		chk.checked = "checked";
  } else {
		state = "closed";
    obj.style.visibility = "hidden";
    obj.style.display = "none";
		chk.checked = "";
  }

	//Hide or unhide all others
	divs = document.getElementsByTagName("div");
	for (i=0; i<divs.length;i++) {
	  obj = divs[i];
    if (obj.id.substring(0,7) == "frmgrp_" && obj.id != "frmgrp_0") {
			if(state == "open") {
				obj.style.visibility = "hidden";
				obj.style.display = "none";
			} else {
				obj.style.visibility = "visible";
				obj.style.display = "block";
			}
		}
	}

}//forAllHeader()










/********************* Ajax *****************************/
var http = createRequestObject();

function createRequestObject(htmlObjectId){
	try {
    request = new XMLHttpRequest();
	} catch (trymicrosoft) {
		try {
			request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (othermicrosoft) {
			try {
        request = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (failed) {
        request = false;
      }
    }
  }

  if (!request)
    alert("Error initializing XMLHttpRequest!");
	else
		return request;
}//createRequestObject()



function sendReq(serverFileName, variableNames, variableValues, handleResponse) {
	var paramString = '';
	
	variableNames = variableNames.split(',');
	variableValues = variableValues.split(',');
	
	for(i=0; i<variableNames.length; i++) {
		paramString += variableNames[i]+'='+variableValues[i]+'&';
	}
	paramString = paramString.substring(0, (paramString.length-1));

	if (paramString.length == 0) {
		http.open('get', serverFileName);
	}
	else {
		http.open('get', serverFileName+'?'+paramString);
	}
  if(handleResponse) http.onreadystatechange = handleResponse;
  http.send(null);
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
			el_id = split[0].trim();
			value = split[1];

			//Erstes Element neu füllen
			element = document.getElementsByName(el_id)[0];
			if(element) $(element).html(value);

			//Zweites Element neu füllen
			if(split[2] && split[3]) {
				el2_id = split[2].trim();
				value2 = split[3];

				element2 = document.getElementsByName(el2_id)[0];
				if(element2) $(element2).html(value2);
			}

			//Drittes Element neu füllen
			if(split[4] && split[5]) {
				el3_id = split[4].trim();
				value3 = split[5];

				//Element neu füllen
				element3 = document.getElementsByName(el3_id)[0];
				if(element3) $(element3).html(value3);
			}

			//post processing for group filter
			if(do_element_post == "filter_group") {
				initList(1, document.getElementsByName('var1')[0]);
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
		$("table.ko_list").find("td").live("click", function() {
			fullid = $(this).attr("id");
			if(typeof(fullid) != "undefined") {
				temp = fullid.split("|");
				table = temp[0]; id = temp[1]; col = temp[2];
				if(id > 0) {
					if($("#chk\\["+id+"\\]").attr("checked")) $("#chk\\["+id+"\\]").attr("checked", false);
					else $("#chk\\["+id+"\\]").attr("checked", true);
				}
			}
		});

		/* Double click on list table cell: Show edit input */
		$("table.ko_list").find("td").live("dblclick", function() {
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
		$(".inlineform textarea, .inlineform input, .inlineform select").live('blur', function() {
			if($(this).hasClass("if-noblur")) return;
			fullid = $(this).parents(".inlineform").attr("id").slice(3);
			sendReq("../inc/ajax.php", "action,id,module,sesid", "inlineformblur,"+fullid+","+kOOL.module+","+kOOL.sid, inlineform_show);
		});

		/* Prevent double click in textarea and input to reload inline editing */
		$(".inlineform textarea, .inlineform input").live('dblclick', function(event) {
			event.cancelBubble;
			event.returnValue = false;
			if(this.is_ie === false) event.preventDefault();
			return false;
		});

		/* Prevent click in textarea and input to trigger checkbox selection for whole row */
		$(".inlineform textarea, .inlineform input").live('click', function(event) {
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
		$(".inlineform textarea, .inlineform input, .inlineform select").live('keyup', function(event) {
			fullid = $(this).parents(".inlineform").attr("id").slice(3);

			if(event.which == 27) {  //ESC
				//Just redraw table cell without storing changes
				sendReq("../inc/ajax.php", "action,id,module,sesid", "inlineformblur,"+fullid+","+kOOL.module+","+kOOL.sid, inlineform_show);
			}
			else if(event.which == 13 && event.shiftKey == false) {  //Enter
				inlineform_submit(this, fullid);
			}
		});
		/* Prevent Enter key from submitting form. keyup above is needed to properly detect ESC
		in all browsers, but then keydown submits form before keyup is handled, where preventions don't work anymore */
		$(".inlineform textarea, .inlineform input, .inlineform select").live('keydown', function(event) {
			if(event.which == 13 && event.shiftKey == false) {
				event.cancelBubble;
        event.returnValue = false;
        if(this.is_ie === false) event.preventDefault();
        return false;
			}
		});


		/* Select changes -> Store changes */
		$(".inlineform select").live('change', function(event) {
			//Don't react to change for doubleselect input
			if($(this).parents(".inlineform").hasClass("if-doubleselect")) return;

			fullid = $(this).parents(".inlineform").attr("id").slice(3);
			inlineform_submit(this, fullid);
		});

		/* Submit button for doubleselect forms clicked -> Store */
		$(".inlineform input[type=button].if_submit").live('click', function(event) {
			fullid = $(this).parents(".inlineform").attr("id").slice(3);
			inlineform_submit(this, fullid);
		});


		//List view: itemlist for columns
		var colitem_timer;
		$("#ko_list_colitemlist_click").live('click', function() {
			if($("#ko_list_colitemlist_flyout").css('display') == 'none') {
				$("#ko_list_colitemlist_flyout").show();
			} else {
				$("#ko_list_colitemlist_flyout").hide();
			}
		});
		$(".flyout_header").live('click', function() {
			$("#ko_list_colitemlist_flyout").hide();
		});

		$("#ko_list_colitemlist_click").live('mouseover', function() {
			$(this).css({backgroundPosition: '-37px 0px'});
		});
		$("#ko_list_colitemlist_click").live('mouseout', function() {
			$(this).css({backgroundPosition: '0px 0px'});
		});


		$("div.input_clearer").click(function() {
			$(this).parent().find('input').val('').submit();
		});


		//Logins: Apply ALL access level to all selects
		$('.access_apply_all').click(function() {
			name = 'sel_'+$(this).attr('id');
			sel = document.getElementsByName(name)[0];
			val = sel.options[sel.selectedIndex].value;
			$(this).closest('div.form_divider').find('select').each(function() {
				$(this).val(val);
			});

		});


		$("input.textmultiplus_new").keypress(function(e) {
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
		$("body").on('click', 'span.form_ft_new', function() {
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
                console.log(kota_ft_alert_no_join_value);
                alert(kota_ft_alert_no_join_value[field]);
            }
            else {
                sendReq("/inc/ajax.php", 'action,field,pid,after,preset_table,join_value_local,join_column_foreign,sesid', 'ftloadpresets,'+field+','+pid+','+after+','+preset_table+','+preset_join_value_local+','+preset_join_column_foreign+','+kOOL.sid, do_element);
            }
            return false;
        });

		$("body").on('click', 'input.form_ft_save', function(event) {
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
			for(i=0; i<fields.length; i++) {
				el = document.getElementsByName('koi['+table+']['+fields[i]+']['+id+']')[0];

				//Treat file uploads separately
				if(el.type == 'file') {
					files = el.files;
					if(files.length > 0) {
						formData.append('koi['+table+']['+fields[i]+']['+id+']', files[0], files[0].name);
					}
					//Check for delete checkbox
					elDel = document.getElementsByName('koi['+table+']['+fields[i]+'_DELETE]['+id+']')[0];
					if($(elDel).attr('checked')) {
						formData.append(fields[i]+'_DELETE', 1);
					}
				} else {
					formData.append(fields[i], $(el).val());
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
			$(this).parents('div.form_ft_row').addClass("form_ft_row_changed");
		});

		$("body").on('click', 'img.form_ft_add', function(event) {
			after = $(this).attr('data-after');
			field = $(this).attr('data-field');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,after,sesid', 'ftnew,'+field+','+pid+','+after+','+kOOL.sid, do_element);
		});

		$("body").on('click', 'img.form_ft_delete', function(event) {
			field = $(this).attr('data-field');
			id = $(this).attr('data-id');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,id,sesid', 'ftdelete,'+field+','+pid+','+id+','+kOOL.sid, do_element);
		});

		$("body").on('click', 'img.form_ft_moveup', function(event) {
			field = $(this).attr('data-field');
			id = $(this).attr('data-id');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,id,direction,sesid', 'ftmove,'+field+','+pid+','+id+',up,'+kOOL.sid, do_element);
		});
		$("body").on('click', 'img.form_ft_movedown', function(event) {
			field = $(this).attr('data-field');
			id = $(this).attr('data-id');
			pid = $(this).attr('data-pid');
			sendReq("/inc/ajax.php", 'action,field,pid,id,direction,sesid', 'ftmove,'+field+','+pid+','+id+',down,'+kOOL.sid, do_element);
		});


		$("body").on('change', "select.sel-peoplefilter", function(event) {
			fid = $(this).val();
			field = $(this).attr("name").substring(18);
			sendReq("/inc/ajax.php", 'action,field,fid,sesid', 'peoplefilterform,'+field+','+fid+','+kOOL.sid, do_element);
		});

		$("body").on('click', ".peoplefilter-submit", function(event) {
			fid = $("select.sel-peoplefilter").val();

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

			neg = $("div.filter-form input[name=filter_negativ]").attr('checked') ? 1 : 0;

			//Add new option to select holding current filters
			newV = fid+'|'+var1+'|'+var2+'|'+var3+'|'+neg;
			text = $("select.sel-peoplefilter option:selected").text()+': ';
			if(neg) text += '!';

			if(text1) text += text1;
			else if(var1) text += var1;

			if(text2) text += ','+text2;
			else if(var2) text += ','+var2;

			if(text3) text += ','+text3;
			else if(var3) text += ','+var3;

			$("select.peoplefilter-act").append($('<option></option>').val(newV).html(text));


			//Set hidden value which will be submitted
			value = '';
			$("select.peoplefilter-act option").each(function() {
				value += $(this).val()+',';
			});
			$("input.peoplefilter-value").val(value.slice(0, -1));

			return false;
		});


		$("div.koi-checkboxes-entry").click(function() {
			if($(this).children("input").attr("checked")) {
				$(this).children("input").attr("checked", false);
				$(this).removeClass("koi-checkboxes-checked");
			} else {
				$(this).children("input").attr("checked", true);
				$(this).addClass("koi-checkboxes-checked");
			}
			value = '';
			$(this).parent("div.koi-checkboxes-container").find("input:checked").each(function() {
				value += (value != '' ? ',' : '') + $(this).val();
			});
			$(this).parent("div.koi-checkboxes-container").children("input.koi-checkboxes-value").val(value);
		});
		//Clicking on checkbox: Reset state and then let code above set the checkbox status
		$("div.koi-checkboxes-entry input").click(function(e) {
			if($(this).is(":checked")) $(this).attr("checked", false);
			else $(this).attr("checked", true);
		});


	});
}


/* Submit changes */
function inlineform_submit(obj, fullid) {
    //Store changes and redraw table cell
    submit_cols = new Array("action", "id", "module", "sesid");
    submit_values = new Array("inlineformsubmit", fullid, kOOL.module, kOOL.sid);
    c = 4;
    $(obj).parents(".inlineform").find("input[type=text][name^=koi], input[type=hidden][name^=koi], textarea[name^=koi]").each(function() {
        submit_cols[c] = $(this).attr("name");
        submit_values[c] = encodeURIComponent($(this).val().replace(new RegExp(",", "g"), '|').replace(new RegExp('\n', "g"), '<br />'));
        c++;
    });
    $(obj).parents(".inlineform").find("select[name^=koi]").each(function() {
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
	//List header with filter function clicked
	$(".ko_listh_filter").live("contextmenu", function(e) {
		$('#ko_listh_filterbox').hide();

		all = $(this).attr('id').substring(6);
		split = all.split(':');
		table = split[0].trim();
		cols = split[1].trim();
		$.get("../inc/ajax.php", {action: "kotafilter", module: kOOL.module, table: table, cols: cols, sesid: kOOL.sid}, function(data) {
			if(data != '') {
				$('#ko_listh_filterbox').css({
					top: e.pageY+'px',
					left: e.pageX+'px'
				}).html(data).show();
			}
		});

    return false;
	});
	//Prevent hiding of filterbox when clicking in box
	$("#ko_listh_filterbox").live("click", function(e) {
		e.stopPropagation();
	});
	//Hide filterbox when clicking on document
	$(document).click(function() {
		$('#ko_listh_filterbox').hide();
	});

	//Filter submission
	$("#kota_filterbox_submit").live("click", function(e) {
		e.preventDefault();

		//Collect all filter inputs and submit them
		submit_cols = new Array("action", "module", "sesid");
		submit_values = new Array("kotafiltersubmit", kOOL.module, kOOL.sid);
		c = 3;

		//Negative checkbox
		submit_cols[c] = "neg";
		submit_values[c] = $("#kota_filterbox_neg").attr("checked") ? 1 : 0;
		c++;

		$(".kota_filter_inputs").each(function() {
			submit_cols[c] = $(this).attr("name");
			submit_values[c] = $(this).val().replace(new RegExp(",", "g"), '|');
			c++;
		});
		//Submit and redraw list
		sendReq("../inc/ajax.php", submit_cols.join(","), submit_values.join(","), do_element);
	});

	//Clear this filters
	$("#kota_filterbox_clear").live("click", function(e) {
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
			value = $(this).children("[selected]").val();
			label = $(this).children("[selected]").attr("label");
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
});





function ko_infobox(mode, text) {
	TINY.box.show({html:text,animate:false,close:false,mask:false,boxid:'ko_infobox_'+mode,autohide:5,top:0});
}//ko_infobox()



function popup(d, w, h) {
	ko_popup(d, w, h);
}


function ko_popup(d, w, h) {
	w = w ? w : 350;
	h = h ? h : 200;
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

			//Optionen splitten und einfüllen
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
