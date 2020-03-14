<script language="javascript" type="text/javascript">
<!--

function do_submit_new_datafield(sesid) {
	do_descr = do_typ = do_options = "";

	var descr = document.formular.txt_new_datafield;
	if(descr) do_descr = escape(descr.value);
	var typ = document.formular.sel_new_datafield;
	if(typ) do_typ = escape(typ.value);

	var reusable = document.formular.chk_new_datafield_reusable;
	if(reusable.checked == true) do_reusable = 1;
	else do_reusable = 0;

	var private = document.formular.chk_new_datafield_private;
	if(private.checked == true) do_private = 1;
	else do_private = 0;

	var preset = document.formular.chk_new_datafield_preset;
	if(preset.checked == true) do_preset = 1;
	else do_preset = 0;

	if(do_typ == "select" || do_typ == "multiselect") {
		var options = document.formular.txt_new_datafield_options;
		if(options) {
			do_options = escape(options.value);
			//while(do_options.indexOf("\n") != -1) do_options = do_options.replace("\n", "%0A");
		} else {
			do_options = '';
		}
	}

	sendReq('../groups/inc/ajax.php', 'action,descr,type,reusable,private,preset,options,sesid', 'adddatafield,'+do_descr+','+do_typ+','+do_reusable+','+do_private+','+do_preset+','+do_options+','+sesid, do_new_datafield);
}//do_submit_new_datafield()


function do_new_datafield() {
	//Loading, Loaded, Interactive
	if(http.readyState == 1 || http.readyState == 2 || http.readyState == 3) {
		//Message-Box einblenden
		msg = document.getElementsByName('wait_message')[0];
		msg.style.visibility = "visible";
    msg.style.display = "block";
		document.body.style.cursor = 'wait';

	//Complete
	} else if(http.readyState == 4) {
		if(http.status == 200) {
			responseText = http.responseText;

			//new option
			split = responseText.split("#");
			value = split[0].trim();
			text = split[1];

			//Add new value
			var $list1 = $(document.getElementsByName('sel_ds1_sel_datafields')[0]);
			var $list2 = $(document.getElementsByName('sel_ds2_sel_datafields')[0]);
			$list1.append(getSelectOption(value, text));
			$list2.append(getSelectOption(value, text));

			// Update hidden value
			var $hid = $(document.getElementsByName('sel_datafields')[0]);
			var vals = [];
			$list2.children().each(function() {
				if ($(this).data('value')) vals.push($(this).data('value'));
			});
			$hid.val(vals.join(','));


			//Message-Box ausblenden
			msg = document.getElementsByName('wait_message')[0];
			msg.style.visibility = "hidden";
			msg.style.display = "none";
			document.body.style.cursor = 'default';
			
		}//if(http.status == 200)
		else if (http.status == 404)
			alert("Request URL does not exist");
  }
}//do_new_datafield()


$(document).ready(function() {
	$("body").on("click", '#chk_groups_dffilter', function(e) {
		if(this.checked == true) {
			jumpToUrl("index.php?action=set_dffilter");
		} else {
			jumpToUrl("index.php?action=unset_dffilter");
		}
	});

	$('input[name="chk_type"]').on('switchChange.bootstrapSwitch', function(event, state) {
		if(state) close_panel('#group_datafields');
		else open_panel('#group_datafields');
	});
});


-->
</script>
