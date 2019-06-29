<script language="javascript" type="text/javascript">
<!--


function rota_init_jsdate() {
	Calendar.setup({"dateFormat":"%d.%m.%Y","ifFormat":"%Y-%m-%d","closeOnClick":true,"align":"Bl","inputField":"f-calendar-field-1","button":"f-calendar-trigger-1"});
}

function eg_doubleselect_add(text, value, name, hid_name) {
	//check for index
	if(value.slice(0, 1) == 'i') {
		sendReq("../rota/inc/ajax.php", "action,gid,element,sesid", "egdoubleselect,"+value.slice(1)+","+name.replace("ds2", "ds1")+",<?php print session_id(); ?>", do_fill_select);
	}
	//real value selected so add it
	else {
		double_select_add(text, value, name, hid_name);
	}
}//eg_doubleselect_add()




$(document).ready(function() {

	//Schedule after selecting an entry in select
	$(".rota-select").live("change", function() {
		t = this.id.split("_");
		event_id = t[0];
		team_id = t[1];
		schedule = this.value;
		sendReq("../rota/inc/ajax.php", "action,eventid,teamid,schedule,module,sesid", "schedule,"+event_id+","+team_id+","+schedule+","+kOOL.module+",<?php print session_id(); ?>", do_element);
	});

	//Delete link for schedulling entries
	$(".rota-entry").live("click", function() {
		t = this.id.split("_");
		event_id = t[2];
		team_id = t[3];
		schedule = t[4];
		sendReq("../rota/inc/ajax.php", "action,eventid,teamid,schedule,module,sesid", "delschedule,"+event_id+","+team_id+","+schedule+","+kOOL.module+",<?php print session_id(); ?>", do_element);
	});

	//Delete link for schedulling entries
	$(".rota-consensus-entry").live("click", function() {
		t = this.id.split("_");
		event_id = t[3];
		team_id = t[4];
		schedule = t[5];
		console.log(t);
		sendReq("../rota/inc/ajax.php", "action,eventid,teamid,schedule,module,sesid", "schedule,"+event_id+","+team_id+","+schedule+","+kOOL.module+",<?php print session_id(); ?>", do_element);
	});

	//Submission of free text
	$(".rota-text").live("keydown", function(event) {
		if(event.which == 13) {  //Enter
			t = this.id.split("_");
			event_id = t[2];
			team_id = t[3];
			schedule = this.value.replace(new RegExp(',', 'g'), '').replace(new RegExp('"', 'g'), '');
			sendReq("../rota/inc/ajax.php", "action,eventid,teamid,schedule,module,sesid", "schedule,"+event_id+","+team_id+","+schedule+","+kOOL.module+",<?php print session_id(); ?>", do_element);

			if(this.is_ie === false) event.preventDefault();
			return false;
		}
	});


	//Show detail options when selecting recipient type in download popup
	$("#recipients").live('change', function() {
		$(".recipients_options").hide();
		$("#options_"+this.value).show();
	});


	//Export select: button and flyout
	$("#export_button").live('mouseover', function() {
		$(this).css({backgroundPosition: '-50px 0px'});
		$("#export_flyout").show();
	});
	$("#export_button").live('mouseout', function() {
		$(this).css({backgroundPosition: '0px 0px'});
		$("#export_flyout").hide();
	});
	$("#export_flyout").live('mouseout', function() {
		$("#export_button").css({backgroundPosition: '0px 0px'});
		$(this).hide();
	});


	$("#btn_save_template").click(function() {
		text = $("#emailtext").val();
		global = $("#preset_global").is(":checked") ? 1 : 0;
		name = $("#save_preset_name").val();

		sendReq("../rota/inc/ajax.php", "action,text,global,name,sesid", "savepreset,"+escape(text)+","+global+","+name+",<?php print session_id(); ?>", do_element);
	});

	if(kOOL.module == 'rota') {
		$('.richtexteditor').ckeditor({customConfig : '/rota/inc/ckeditor_custom_config.js' });
	}
});

-->
</script>
