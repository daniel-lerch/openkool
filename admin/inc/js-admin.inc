<script language="javascript" type="text/javascript">
	<!--

	$(document).ready(function() {
		if(kOOL.module == 'admin') {
			$('.richtexteditor').ckeditor({customConfig : '/admin/inc/ckeditor_custom_config.js' });
		}

		$("body").on("click", ".panel-heading[id^='group_']", function() {
			var group = $(this).find("input[type='checkbox'][name^='module_install_status']").prop("name").substring(22).slice(0, -1);
			$("#group_" + group + "_content").slideToggle().toggleClass("in");
			$("#group_" + group).toggleClass("panel-primary").toggleClass("panel-info");

			var checkbox = $("input[name='module_install_status[" + group + "]']");
			if($("#group_" + group).hasClass("panel-primary")) {
				checkbox.prop("checked", true);
			} else {
				checkbox.prop("checked", false);
			}
			$(this).parent().find('.slider').each(function() {
				var id = $(this).prop("id").substring(6);
				$("#" + id).bootstrapSlider("refresh");
			});
		});


		//Mark text describing the different access levels according to current access settings
		$(document).on("change", "input[name^='sel_rechte']", function(event) {
			module = $(this).closest(".module").prop('id').substring(6);
			level = $(this).val();

			//Check if changed slider is the ALL slider
			isAll = ($(this).prop('name').substr(-2) == '_0') || ($(this).prop('name').substr(-1*module.length) == module);
			
			if(isAll) {  //all slider
				if(level == 0) {
					//Level 0: Only mark 0
					$("span.accessLevel.module-"+module).removeClass('active');
					$("span.accessLevel.module-"+module+".level-0").addClass('active');
				} else {
					//Mark every level from 1 to selected level (exclude 0)
					$("span.accessLevel.module-"+module).removeClass('active');
					for(i=1;i<=level;i++) {
						$("span.accessLevel.module-"+module+".level-"+i).addClass('active');
					}
				}
			}
			else {  //detail slider
				//Find ALL value from ALL slider
				allSel = "input[name='sel_rechte_"+module+"_0'";
				allValue = Math.abs($(allSel).val());

				//Find max value of all partial sliders
				maxValue = 0;
				$(this).closest('.panel-body').find('.slider').each(function() {
					var id = $(this).prop("id").substring(6);
					maxValue = Math.max(maxValue, $('#' + id).bootstrapSlider('getValue'));
				});
				maxValue = Math.abs(maxValue);

				//Mark texts above ALL level with partial class
				if(maxValue == 0) {
					$("span.accessLevel.module-"+module).removeClass('partial');
				} else {
					$("span.accessLevel.module-"+module).removeClass('partial');
					for(j=(allValue+1);j<=maxValue;j++) {
						$("span.accessLevel.module-"+module+".level-"+j).addClass('partial');
					}
				}
			}
		});


		$(document).on("change", "input[name^='sel_rechte'][name$='_0']", function(event) {
			var module = $(this).closest(".module");
			if($(module).find("[id^='accessrights_']").hasClass("in")) {
				update_all_slider(this);
			} else {
				update_all_slider(this, true);
			}
		});

		$(document).on('keypress', '.slider', function(key) {
			if($.isNumeric(key.key)) {
				var newValue = key.key;
				var id = $(this).prop("id").substring(6);
				if(newValue <= $("#" + id).data('bootstrapSlider').getAttribute("max")) {
					$("#" + id).bootstrapSlider('setValue', newValue);
					$("#sel_recht_" + id).val(newValue);
				}
			}
		});
	});

	function update_all_slider(module, force_update = false) {
		var parentId = $(module).attr("id");
		var newValue = $("#" + parentId).val();
		$(module).closest('.panel-body').find('.panel-secondary .slider').each(function() {
			var id = $(this).prop("id").substring(6);
			if(force_update || $('#' + id).bootstrapSlider('getValue') < newValue) {
				$("#" + id).bootstrapSlider("setValue", newValue);
				$("#sel_recht_" + id).val(newValue);
			}
		});
		return false;
	}

	-->
</script>
