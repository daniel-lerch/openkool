<script language="javascript" type="text/javascript">
<!--
function tracking_entered_simple(data) {
	split = data.split('@@@');
	$("#"+split[0]).html(split[1]).fadeTo(500, 1).delay(2000).fadeTo(500, 0);

	//Change the value of the checkbox
	if($("#"+split[0].replace('tstate', 'chk')).val() == 1) {
		$("#"+split[0].replace('tstate', 'chk')).val(0);
	} else {
		$("#"+split[0].replace('tstate', 'chk')).val(1);
	}
}

function tracking_entered_value(data) {
	split = data.split('@@@');
	$("#"+split[0]).html(split[1]).fadeTo(500, 1).delay(2000).fadeTo(500, 0);
}

function tracking_entered_value_type(data) {
	split = data.split('@@@');
	$("#"+split[0]).html(split[1]);
}


function tracking_ds_filter(text, value, name, hid_name) {
	if(text.slice(0, 3) == "---") return false;
	else double_select_add(text, value, name, hid_name);
}


$(document).ready(function() {
	//Add link to zip export icons which will get all selected trackings
	$("#export_tracking_xls_zip, #export_tracking_pdf_zip").click(function(event) {
		var ids = '';
		$.each($("input[name^='chk[']:checked"), function(key, obj) {
			ids += obj.name.replace('chk[', '').replace(']', '')+',';
		});
		ids = ids.slice(0, -1);
		set_hidden_value('id', ids);
	});

	//Add link to all add icons to show the select and input for "type mode"
	$("a[id^='addlink_'], img[id^='addlink_']").live('click', function() {
		$("#"+$(this).attr('id').replace('addlink_', 'adddiv_')).slideToggle(200);
		$("#"+$(this).attr('id').replace('addlink_', 'adddiv_')+" select").focus();
	});

	//Button to activate all entries for one date (simple)
	$("#main_content").on("click", "img.tracking_set_simple_for_all", function() {
		c = confirm('<?php print getLL('tracking_confirm_set_for_all'); ?>');
		if(c) {
			temp = $(this).attr('id').split('_');
			tid = temp[1];
			date = temp[2];
			sendReq('../tracking/inc/ajax.php', 'action,tid,date,sesid', 'settrackingall,'+tid+','+date+','+kOOL.sid, do_element);
		}
	});

	//Button to activate all entries for one date (value, valueNonNum)
	$("#main_content").on("keydown", "input.tracking_set_value_for_all", function(e) {
		if(e.which == 13) {
			c = confirm('<?php print getLL('tracking_confirm_set_for_all'); ?>');
			if(c) {
				temp = $(this).attr('id').split('_');
				tid = temp[2];
				date = temp[3];
				value = this.value;
				sendReq('../tracking/inc/ajax.php', 'action,tid,date,value,sesid', 'settrackingall,'+tid+','+date+','+value+','+kOOL.sid, do_element);
			}
			e.preventDefault();
			return false;
		} else {
			return true;
		}
	});

	//Button to activate all entries for one date (typecheck)
	$("#main_content").on("click", "img.tracking_set_typecheck_for_all, span.tracking_set_typecheck_for_all", function() {
		c = confirm('<?php print getLL('tracking_confirm_set_for_all'); ?>');
		if(c) {
			temp = $(this).attr('id').split('_');
			tid = temp[2];
			date = temp[3];
			do_type = temp[4];
			if(do_type == undefined) do_type = '';
			sendReq('../tracking/inc/ajax.php', 'action,tid,date,type,sesid', 'settrackingall,'+tid+','+date+','+do_type+','+kOOL.sid, do_element);
		}
	});

	//Button to clear all entries for one date
	$("#main_content").on("click", "img.tracking_del_for_all", function() {
		c = confirm('<?php print getLL('tracking_confirm_del_for_all'); ?>');
		if(c) {
			temp = $(this).attr('id').split('_');
			tid = temp[1];
			date = temp[2];
			do_type = temp[3];
			if(do_type == undefined) do_type = '';
			sendReq('../tracking/inc/ajax.php', 'action,tid,date,type,sesid', 'deltrackingall,'+tid+','+date+','+do_type+','+kOOL.sid, do_element);
		}
	});

	//Set default values for a person
	$("#main_content").on("click", "img.tracking_default", function() {
		c = confirm('<?php print getLL('tracking_confirm_default'); ?>');
		if(c) {
			temp = $(this).attr('id').split('_');
			tid = temp[2];
			lid = temp[3];
			sendReq('../tracking/inc/ajax.php', 'action,tid,lid,sesid', 'setdefault,'+tid+','+lid+','+kOOL.sid, do_element);
		}
	});
});
-->
</script>
