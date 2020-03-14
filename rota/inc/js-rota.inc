<script language="javascript" type="text/javascript">
<!--


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
	$("body").on("changed.bs.select", '.rota-select', function(event, clickedIndex, newValue, oldValue) {
		t = this.id.split("_");
		event_id = t[0];
		team_id = t[1];
		schedule = $(this).val();
		if (event_id && team_id && schedule) {
			sendReq("../rota/inc/ajax.php", "action,eventid,teamid,schedule,module,sesid", "schedule," + event_id + "," + team_id + "," + schedule + "," + kOOL.module + ",<?php print session_id(); ?>", do_element);
		}
	});

	//Delete link for schedulling entries
	$('body').on("click", ".rota-entry", function() {
		t = this.id.split("_");
		event_id = t[2];
		team_id = t[3];
		schedule = t[4];
		sendReq("../rota/inc/ajax.php", "action,eventid,teamid,schedule,module,sesid", "delschedule,"+event_id+","+team_id+","+schedule+","+kOOL.module+",<?php print session_id(); ?>", do_element);
	});

	//Delete link for schedulling entries
	$('body').on("click", ".rota-consensus-entry", function() {
		t = this.id.split("_");
		event_id = t[3];
		team_id = t[4];
		schedule = t[5];
		sendReq("../rota/inc/ajax.php", "action,eventid,teamid,schedule,module,sesid", "schedule,"+event_id+","+team_id+","+schedule+","+kOOL.module+",<?php print session_id(); ?>", do_element);
	});

	//Submission of free text
	$('body').on("keydown", ".rota-text", function(event) {
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
	$('body').on('change', "#recipients", function() {
		$(".recipients_options").hide();
		$("#options_"+this.value).show();
	});


	$("#btn_save_template").click(function() {
		text = $("#emailtext").val();
		global = $("#preset_global").is(":checked") ? 1 : 0;
		name = $("#save_preset_name").val();

		sendReq("../rota/inc/ajax.php", ["action", "text", "global", "name", "sesid"], ["savepreset", text, global, name, "<?php print session_id(); ?>"], do_element);
	});

	if(kOOL.module == 'rota') {
		$('.richtexteditor').ckeditor({customConfig : '/rota/inc/ckeditor_custom_config.js' });
	}

	$('body').on('mouseover', '.rota-filesend-person-preview', function() {
		var $this = $(this);
		if (!$this.data('utd')) rota_filesend_load_preview($this, $this.attr('data-id'));
	});
	$('body').on('change', '#recipients, #recipients_group, [name="single_id"], [name="sel_teams_members"], [name="sel_teams_leaders"], [name="sel_teams_schedulled"]', function() {
		rota_filesend_update_recipients();
	});
	$('body').on('input', '[name="subject"]', function() {
		rota_filesend_invalidate_previews();
	});
	if (typeof(CKEDITOR) != 'undefined' && typeof(CKEDITOR.instances['emailtext']) != 'undefined') {
		CKEDITOR.instances['emailtext'].on('change', function() {
			rota_filesend_invalidate_previews();
		});
	}

	rota_filesend_update_recipients();
});


function rota_filesend_update_recipients() {
	var params = $('form[name="formular"]').toAssocArray();
	params.action = 'filesendpreviewrecs';
	params.sesid = kOOL.sid;
	$.post('../rota/inc/ajax.php', params).done(function(data) {
		$('#filesend_preview').html(data);
	});
}
function rota_filesend_invalidate_previews() {
	$('.rota-filesend-person-preview')
		.attr('data-original-title', '<i class=&quot;fa fa-pulse fa-spinner&quot;></i>')
		.tooltip('fixTitle')
		.data('utd', false);
}
function rota_filesend_load_preview($this, id) {
	var params = $('form[name="formular"]').toAssocArray();
	params.action = 'filesendpreview';
	params.sesid = kOOL.sid;
	params.recipient_id = id;
	$.post('../rota/inc/ajax.php', params).done(function(data) {
		$this
			.attr('data-original-title', data)
			.tooltip('fixTitle')
			.data('utd', true)
			.tooltip('show');
	});
}

-->
</script>
