<script language="javascript" type="text/javascript">
<!--
function tracking_entered_simple(data) {
	var split = data.split('@@@');
	$('[id="'+split[0]+'"]').addClass(split[1]);
	setTimeout(function() {
		$('[id="'+split[0]+'"]').removeClass(split[1]);
	}, 600);

	//Change the value of the checkbox
	if($('[id="'+split[0].replace('tstate', 'chk')+'"]').val() == 1) {
		$('[id="'+split[0].replace('tstate', 'chk')+'"]').val(0);
	} else {
		$('[id="'+split[0].replace('tstate', 'chk')+'"]').val(1);
	}
}

function tracking_entered_value(data) {
	var split = data.split('@@@');
	$("#"+split[0]).addClass(split[1]);
	setTimeout(function() {
		$("#"+split[0]).removeClass(split[1]);
	}, 600);
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
	$('body').on('click', "a[id^='addlink_'], img[id^='addlink_'], i[id^='addlink_']", function() {
		$("#"+$(this).attr('id').replace('addlink_', 'adddiv_')).slideToggle(200);
		$("#"+$(this).attr('id').replace('addlink_', 'adddiv_')+" select").focus();
	});

	//Button to activate all entries for one date (simple)
	$("#main_content").on("click", ".tracking_set_simple_for_all", function() {
		c = confirm('<?php print getLL('tracking_confirm_set_for_all'); ?>');
		if(c) {
			temp = $(this).attr('id').split('_');
			tid = temp[1];
			date = temp[2];
			sendReq('../tracking/inc/ajax.php', 'action,tid,date,sesid', 'settrackingall,'+tid+','+date+','+kOOL.sid, do_element);
		}
	});

	//Button to activate all entries for one date (value, valueNonNum)
	$("#main_content").on("keydown", ".tracking_set_value_for_all", function(e) {
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
	$("#main_content").on("click", ".tracking_set_typecheck_for_all, .tracking_set_typecheck_for_all", function() {
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
	$("#main_content").on("click", ".tracking_del_for_all", function() {
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
	$("#main_content").on("click", ".tracking_default", function() {
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

<?php if(ko_get_setting('qz_tray_enable')) { ?>
	<script src="/inc/qz-tray/rsvp-3.1.0.min.js" charset="UTF-8"></script>
	<script src="/inc/qz-tray/sha-256.min.js" charset="UTF-8"></script>
	<script src="/inc/qz-tray/qz-tray.js" charset="UTF-8"></script>
	<script>
		qz.security.setCertificatePromise(function(resolve,reject) {
			$.ajax({url:'/inc/ajax.php',cache:false,dataType:'text',data:{sesid:'<?= session_id() ?>',action:'getcert'}}).then(resolve,reject);
		});
		qz.security.setSignaturePromise(function(toSign) {
			return function(resolve,reject) {
				$.ajax({url:'/inc/ajax.php',data:{sesid:'<?= session_id() ?>',action:'rsasign',sign:toSign}}).then(resolve,reject);
			};
		});
		$(document).ready(function() {
			var checkinLinks = $('.qzCheckinLinks');
			if(checkinLinks.length) {
				qz.websocket.connect({host:'<?= ko_get_setting('qz_tray_host') ?: 'localhost' ?>'}).then(function() {
					qz.printers.find().then(function(data) {
						data.forEach(function(name) {
							checkinLinks.each(function() {
								$(this).append(
									$('<a>').attr('href','/checkin?t='+$(this).data('id')+'&qzp='+name).text(name),
									'<br/>'
								);
							});
						});
					});
				});
			}
		});
	</script>
<?php } ?>
