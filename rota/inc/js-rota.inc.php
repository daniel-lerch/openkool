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
		schedule = t.slice(4).join('_');
		sendReq("../rota/inc/ajax.php", "action,eventid,teamid,schedule,module,sesid", "delschedule,"+event_id+","+team_id+","+schedule+","+kOOL.module+",<?php print session_id(); ?>", do_element);
	});

	$('body').on("change", ".rota-schedule input[name=\"rota_entry_daysrange\"]", function() {

		var schedule = $(this).val();
		schedule = schedule.replace(/,/g,"-");

		var id = this.id.split("_");
		var event_id = id[2];
		var team_id = id[3];
		var person_id = id[4];

		if(person_id === "freetexttemplate") {
			var freetext = prompt("Namen eingeben");
			if(freetext === null || freetext === "") return false;

			if (freetext.search(/[^a-zA-Z] +/) !== -1) {
				alert("<?=getLL("rota_schedule_only_characters_allowed"); ?>");
				return false;
			}

			person_id = freetext;
		}

		sendReq("../rota/inc/ajax.php", "action,eventid,teamid,personid,schedule,module,sesid", "schedule,"+event_id+","+team_id+","+person_id+","+schedule+",rota,<?php print session_id(); ?>", do_element);
	});

	//Delete link for schedulling entries
	$('body').on("click", ".rota-consensus-entry", function() {
		t = this.id.split("_");
		event_id = t[3];
		team_id = t[4];
		schedule = t.slice(5).join('_');
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

	$('body').on("click", "#btn_rota_xls_download", function() {
		np_ids = $("#recipient_nok_ids").val();
		sendReq("../rota/inc/ajax.php", ["action", "text", "sesid"], ["nomails_xls_download", np_ids, "<?php print session_id(); ?>"], show_box);
	});

	$('body').on("click", "#btn_rota_add_to_mylist", function() {
		np_ids = $("#recipient_nok_ids").val();
		sendReq("../rota/inc/ajax.php", ["action", "text", "sesid"], ["rota_add_to_mylist", np_ids, "<?php print session_id(); ?>"], do_element);
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

	$(document).tooltip({
		selector:'.rota-tooltip',
		html:true,
		container:'body',
		title:function() {
			var code = $(this).data('tooltip-code');
			if(code && !$(this).data('tooltip-show-minigraph')) {
				return code;
			} else if($(this).data('tooltip-url')) {
				var minigraph = new Image();
				minigraph.src = "/images/spinner.svg";
				minigraph.width = $(this).data('tooltip-width');
				minigraph.height = $(this).data('tooltip-height');
				var loadImage = new Image();
				loadImage.onload = function() {
					minigraph.replaceWith(loadImage);
				};
				loadImage.src = $(this).data('tooltip-url');
				var image = "<img src='" + loadImage.src + "' style='width:"+ minigraph.width +"px; height:"+ minigraph.height +"px'>";

				if($(this).data('tooltip-combine-text') && code) {
					var content = image + code;
				} else {
					var content = image;
				}

				return "<div class='rota-tooltip-inner " + (minigraph.width===438 ? "-full" : "-half") + "'>" + content + "</div>";
			}
		},
	});
	$(document).on('show.bs.tooltip','.rota-consensus-entry.rota-tooltip',function() {
		$(this).on('remove',function() {
			$(this).tooltip('destroy');
		});
	});
	$(document).on('show.bs.tooltip','.daysrange .rota-tooltip',function() {
		$(this).on('remove',function() {
			$(this).tooltip('destroy');
		});
	});

	$('body').on('click', '#planning_list .team_name', function() {
		var team_id = $(this).parent().data('team');
		$('#itemlist_teams_' + team_id).click();
	});

	$('body').on('dblclick', '#planning_list .member_event_info', function() {
		if($(this).hasClass("consensus-disabled")) return false;

		var eventid = $(this).data("event");
		var teamid = $(this).data("team");
		var personid = $(this).data("member");
		var scheduled = $(this).data("consensus-scheduled");

		if(scheduled == 1) {
			sendReq("/rota/inc/ajax.php", "action,eventid,teamid,schedule,type,module,sesid", "delschedule,"+eventid+","+teamid+","+personid+",planning,"+kOOL.module+","+kOOL.sid, do_element);
			$(this).data("consensus-scheduled", "0");
		} else {
			sendReq("/rota/inc/ajax.php", "action,eventid,teamid,schedule,type,module,sesid", "schedule,"+eventid+","+teamid+","+personid+",planning,"+kOOL.module+","+kOOL.sid, do_element);
			$(this).data("consensus-scheduled", "1");
		}
	});

	$('body').on('mouseover', '#planning_list .col', function(e) {
		$('.col.col-'+$(this).data('event')).addClass('highlight');
		$(".team_member[data-team='" + $(this).data('team') +"'] .member_name[data-member='" + $(this).data('member') +"'] ").addClass('highlight');
	});
	$('body').on('mouseout', '#planning_list .col', function(e) {
		$('.col.col-'+$(this).data('event')).removeClass('highlight');
		$(".team_member[data-team='" + $(this).data('team') +"'] .member_name[data-member='" + $(this).data('member') +"'] ").removeClass('highlight');
	});
});


function update_team_status(event_id, team_id, status) {
	fields = $('.team_member').find('[data-event="'+event_id+'"][data-team="'+team_id+'"]')
	$(fields).each(function(id,field) {
		$(field).toggleClass("consensus-disabled");
	});

}

function rota_planning_list_init() {

	$('#planning_list').find('.member_event_info').each(function() {
		var status = $(this).data("consensus-status");
		var scheduled = $(this).data("consensus-scheduled");

		if(scheduled === 1) {
			$(this).find("i.fa").addClass("fa-circle");
		} else {
			$(this).find("i.fa").addClass("fa-circle-thin");
		}
	});

	$('#planning_list').find('.closed').each(function() {
		var team_id = $(this).data('team');
		$('#planning_list .team_member[data-team="'+team_id+'"]').hide();
	});
}


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
