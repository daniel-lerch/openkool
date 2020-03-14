<?php

function listGroups(&$groups,$pid = '',$level = 0) {
	foreach($groups[$pid] as $id => $group) {
		$hasChildren = !empty($groups[$id]) || !empty($group['roles']);
		?>
			<li class="list-group-item<?= $group['placeholder'] ? ' unselectable' : ''?>" data-field="g<?= $id ?>" style="padding-left:<?= 15+10*$level ?>px; <?= $level > 0 ? ' border-top:1px solid #ddd;' : '' ?>" href="#subscriptionFormSubgroupList<?= $id ?>" data-toggle="collapse">
				<span class="<?= $hasChildren ? 'glyphicon glyphicon-chevron-right' : '' ?>" style="width:15px; display:inline-block;"> </span>
				<?= $group['name'] ?>
			</li>
			<?php if($hasChildren) { ?>
				<ul class="list-group collapse" id="subscriptionFormSubgroupList<?= $id ?>" style="margin-bottom:0;">
					<?php if(!empty($group['roles'])) { foreach($group['roles'] as $roleId => $role) { ?>
						<li class="list-group-item" data-field="g<?= $id ?>:r<?= $roleId ?>" style="padding-left:<?= 25+15*$level ?>px; border-top:1px solid #ddd;">
						<span class="" style="width:15px; display:inline-block;"></span>
						<?= $group['name'].': '.$role ?>
					<?php }} ?>
					<?php listGroups($groups,$id,$level+1); ?>
				</ul>
			<?php } ?>
		<?php
	}
}

?>
<div class="row koSubscriptionFormEdit" style="display:flex; margin-bottom:1em;">
	<input type="hidden" name="<?= $inputName?>" value="" />
	<div class="col-md-6">
		<div class="panel-group" style="min-height:1em; border:1px dashed silver; padding:5px; height:100%; margin:0;" id="subscriptionFormLayout">

		</div>
	</div>
	<div class="col-md-6" id="subscriptionFormFieldSources">
		<div class="panel-group" id="subscriptionFormFieldSelect" style="margin:0;">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#subscriptionFormFieldSelect" href="#subscriptionFormLeuteFields"><?= getLL('subscription_fields_edit_person_fields') ?></a>
					</h4>
				</div>
				<div id="subscriptionFormLeuteFields" class="panel-collapse collapse">
					<ul class="list-group formFieldSource">
						<?php foreach($leuteFields as $field) { ?>
							<?php if($ll = trim(getLL('kota_ko_leute_'.$field))) { ?>
								<?php
									$data = ['field' => $field,'type' => $KOTA['ko_leute'][$field]['form']['type']];
									if($data['type'] == 'textplus') {
										$data['textplus-options'] = htmlentities(json_encode_latin1(db_select_distinct('ko_leute', $field, "", $KOTA['ko_leute'][$field]['form']['where'], $KOTA['ko_leute'][$field]["form"]["select_case_sensitive"] ? TRUE : FALSE)),ENT_COMPAT,'ISO-8859-1');
									}
								?>
								<li class="list-group-item" <?= implode(' ',array_map(function($k,$v) {return 'data-'.$k.'="'.$v.'"';},array_keys($data),$data)) ?>><?= $ll ?></li>
							<?php } ?>
						<?php } ?>
					</ul>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#subscriptionFormFieldSelect" href="#subscriptionFormGroups"><?= getLL('subscription_fields_edit_groups') ?></a>
					</h4>
				</div>
				<div id="subscriptionFormGroups" class="panel-collapse collapse">
					<ul class="list-group formFieldSource clone">
						<?php listGroups($groups); ?>
					</ul>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#subscriptionFormFieldSelect" href="#subscriptionFormDataFields"><?= getLL('subscription_fields_edit_datafields') ?></a>
					</h4>
				</div>
				<div id="subscriptionFormDataFields" class="panel-collapse collapse">
					<ul class="list-group formFieldSource">
					</ul>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#subscriptionFormFieldSelect" href="#subscriptionFormSpecial"><?= getLL('subscription_fields_edit_special') ?></a>
					</h4>
				</div>
				<div id="subscriptionFormSpecial" class="panel-collapse collapse">
					<ul class="list-group formFieldSource multiple">
						<li class="list-group-item" data-field="_caption"><?= getLL('subscription_fields_edit_caption') ?></li>
						<li class="list-group-item" data-field="_text"><?= getLL('subscription_fields_edit_text') ?></li>
						<li class="list-group-item" data-field="_hr"><?= getLL('subscription_fields_edit_hr') ?></li>
						<li class="list-group-item" data-field="_check"><?= getLL('subscription_fields_edit_check') ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
function SubcriptionFormFieldSelect() {

	var allGroups = <?= json_encode_latin1(array_map(function($g) {return array('name' => $g['name'],'datafields' => $g['datafields'] ? explode(',',$g['datafields']) : [],'placeholder' => $g['placeholder']);},$all_groups)) ?>;

	var datafields = <?= json_encode_latin1($datafields) ?>;

	var fields = <?= $fields ?>;

	var removeIntent = false;

	var groupSelectInput = $('#subscriptionFormLayout').closest('form').find("[name^='koi[ko_subscription_forms][groups]']");

	var getGroupId = function(field) {
		var m = field.match(/^g([0-9]{6})/);
		if(m) {
			return m[1];
		}
		return false;
	}

	this.updateDatafields = function() {
		var available = [];
		var selected = [];
		var datafieldPanels = $();
		var groupsWithAllDatafieldPanels = [];
		var groupPanels = {};
		var groups = [];
		$('#subscriptionFormLayout').children().not('.ui-sortable-placeholder').each(function() {
			var field = $(this).data('field');
			var groupId = getGroupId(field);
			if(groupId && (field.length == 7 || field.substr(7,2) == ':r')) {
				available.push('g'+groupId+':dADDALL');
				var dfids = allGroups[groupId].datafields;
				if(dfids) {
					dfids.forEach(function(id) {
						if(datafields.hasOwnProperty(id)) {
							available.push('g'+groupId+':d'+id);
						}
					});
					$(this).removeClass('panel-danger').addClass('panel-default');
				}
				groupPanels[groupId] = this;
				groups.push(groupId);
			} else {
				var field = $(this).data('field');
				if(field.match(/^g[0-9]{6}:d([0-9]{6}|ADDALL)$/)) {
					selected.push(field);
					datafieldPanels = datafieldPanels.add(this);
					if(field.substr(9,6) == 'ADDALL') {
						groupsWithAllDatafieldPanels.push(getGroupId(field));
					}
					$(this).removeClass('panel-danger').addClass(field.substr(9,6) == 'ADDALL' ? 'panel-info' : 'panel-default');
				}
			}

		});
		groupSelectInput.val().split(',').forEach(function(field) {
			if(field) {
				var a = field.split(':');
				var groupId = a[0].substring(1);
				var roleId = false;
				if(a.length > 1) {
					roleId = a[1].substring(1);
				}
				if(groupPanels.hasOwnProperty(groupId)) {
					$(groupPanels[groupId]).removeClass('panel-default panel-info').addClass('panel-danger');
				}
				if(available.indexOf('g'+groupId+':dADDALL') == -1) {
					available.push('g'+groupId+':dADDALL');
				}
				var dfids = allGroups[groupId].datafields;
				if(dfids) {
					dfids.forEach(function(id) {
						var dfid = 'g'+groupId+':d'+id;
						if(available.indexOf(dfid) == -1 && datafields.hasOwnProperty(id)) {
							available.push(dfid);
						}
					});
				}
				groups.push(groupId);
			}
		});
		$('#subscriptionFormGroups .list-group-item').each(function() {
			var field = $(this).data('field');
			if(field) {
				var groupId = getGroupId(field);
				var disable = groups.indexOf(groupId) != -1;
				$(this).toggleClass('disabled',disable);
				$(this).draggable('option','disabled',disable || $(this).hasClass('unselectable'));
			}
		});
		datafieldPanels.each(function() {
			var field = $(this).data('field');
			if(field.substr(9,6) != 'ADDALL' && groupsWithAllDatafieldPanels.indexOf(getGroupId(field)) != -1) {
				$(this).removeClass('panel-default').addClass('panel-danger');
			}
		});
		var selectItems = $.map(available,function(id) {
			var e = $('<li>');
			e.addClass('list-group-item');
			e.attr('data-field',id);
			if(id.substr(9,6) == 'ADDALL') {
				e.text(allGroups[id.substr(1,6)].name+': <?= getLL('subscription_fields_edit_all_datafields') ?>');
			} else {
				e.text(allGroups[id.substr(1,6)].name+': '+datafields[id.substr(9,6)].description);
			}
			var selIndex = selected.indexOf(id);
			if(selIndex != -1) {
				e.hide();
				selected.splice(selIndex,1);
				datafieldPanels.splice(selIndex,1);
			}
			initDraggable(e);
			if(groupsWithAllDatafieldPanels.indexOf(id.substr(1,6)) != -1) {
				e.addClass('disabled');
				e.draggable('option','disabled',true);
			}
			return e;
		});
		datafieldPanels.removeClass('panel-default panel-info').addClass('panel-danger');
		$('#subscriptionFormDataFields .formFieldSource').children().remove();
		$('#subscriptionFormDataFields .formFieldSource').append(selectItems);
	}

	this.initDraggable = function(elem) {
		var source = elem.closest('.formFieldSource');
		elem.draggable({
			connectToSortable:'#subscriptionFormLayout',
			revert:'invalid',
			revertDuration:200,
			helper:function(event) {
				return createFormElement(elem);
			},
			start:function(event,ui) {
				if(!source.hasClass('clone') && !source.hasClass('multiple')) {
					$(event.target).hide();
				}
			},
			stop:function(event,ui) {
				onAddToForm(ui.helper,$(event.target));
				ui.helper.children('.panel-body').show();
			},
			containment:elem.closest('.formular-cell'),
			disabled:elem.hasClass('unselectable'),
		});
	}

	this.createFormElement = function(elem,values = null) {
		var field = elem.data('field');
		var label = elem.text().trim();
		if(field == '_hr') label = '<div style="border-bottom:solid #000 2px; height:0px; margin-right:10px;"></div>';
		if(field == '_caption' && values) label = values;
		var style = 'default';
		if(field == '_caption') style = 'primary';
		if(field.match(/^g[0-9]{6}:dADDALL$/)) style = 'info';
		var e = $('<div class="panel panel-'+style+' subscriptionFormLayoutElement"><div class="panel-heading"><h4 class="panel-title">'+label+'</h4></div></div>');
		e.data(elem.data());
		e.width(elem.width());
		e.append(createElementForm(field,label,values,elem.data()).hide());
		return e;
	}

	this.onAddToForm = function(elem,srcElem) {
		var source = srcElem.closest('.formFieldSource');
		if(!source.hasClass('clone') && !source.hasClass('multiple')) {
			srcElem.hide();
		}
		var markAsMandatory = function(event,state) {
			var title = $(this).closest('.panel').find('.panel-title');
			if(state) {
				title.append($('<span class="pull-right mandatoryIndicator glyphicon glyphicon-asterisk"></span>'))
			} else {
				$('.mandatoryIndicator',title).remove();
			}
		};
		$('.mandatoryInputGroup input',elem).on('switchChange.bootstrapSwitch init.bootstrapSwitch',markAsMandatory);
		$('.panel-body.alwaysMandatory',elem).each(markAsMandatory);
		$('input[type=checkbox]',elem).bootstrapSwitch({
			onText:'<?= getLL('yes') ?>',
			offText:'<?= getLL('no') ?>',
			size:'small',
		});
		if(elem.parent('#subscriptionFormLayout').length === 0) {
			srcElem.show();
		} else {
			if(!source.hasClass('multiple')) {
				srcElem.draggable('disable');
				srcElem.addClass('disabled');
			}
		}
		if(getGroupId(elem.data('field'))) {
			updateDatafields();
		}
	}

	// build form for inline options (label, mandatory, ...)
	this.createElementForm = function(field,label,values,data) {
		var e = $('<div class="panel-body">');
		if(field[0] == '_') {
			var c = 0;
			var others = $('#subscriptionFormLayout :input').toArray();
			while(others.some(function(input) {
				return input.name.startsWith('<?= $inputName ?>['+field+c+']');
			})) {
				c = (c+1)%1000;
			}
			var name = '<?= $inputName ?>['+field+c+']';
			if(field == '_caption') {
				var input = $('<input name="'+name+'" class="form-control">');
				input.change(function() {
					$(this).closest('.panel').find('.panel-title').text($(this).val());
				});
				input.val(values ? values : label);
				e.append(input);
			} else if(field == '_text') {
				e.append($('<textarea name="'+name+'" class="form-control">').css('resize','vertical').val(values ? values : label));
			} else if(field == '_hr') {
				e = $('<input type="hidden" name="'+name+'" value="1" />');
			} else if(field == '_check') {
				var name = '<?= $inputName ?>['+field+c+']';
				e.append($('<textarea name="'+name+'" class="rte"></textarea>').val(values));
				e.addClass('alwaysMandatory');
			}
		} else {
			if(field.match(/^g[0-9]{6}:dADDALL$/)) {
				var dfCheckboxes = $('<div>');
				var excludedDatafields = values && values.hasOwnProperty('excludeDatafields') ? values.excludeDatafields : [];
				var optionCheckboxes = $('<div>');
				var excludedOptions = values && values.hasOwnProperty('excludeOptions') ? values.excludeOptions : {};
				allGroups[field.substr(1,6)].datafields.forEach(function(dfid) {
					if(datafields.hasOwnProperty(dfid)) {
						dfCheckboxes.append(
							$('<div>')
								.append(
									$('<input>')
										.addClass('form-control')
										.attr({
											type:'checkbox',
											name:'<?= $inputName ?>['+field+'][includeDatafields][]',
											value:dfid,
										})
										.prop('checked',excludedDatafields.indexOf(dfid) == -1)
								)
								.append(
									$('<span>').text(datafields[dfid].description).css({'margin-left':'0.5em'})
								)
								.append(
									$('<i class="text-muted">').text(' (<?= getLL('subscription_fields_edit_placeholder') ?>: ###'+field.substr(0,9)+dfid+'###)')
								)
						);
						if(datafields[dfid].type == 'select' || datafields[dfid].type == 'multiselect') {
							datafields[dfid].options.forEach(function(option) {
								optionCheckboxes.append(
									$('<div>')
										.append(
											$('<input>')
												.addClass('form-control')
												.attr({
													type:'checkbox',
													name:'<?= $inputName ?>['+field+'][includeOptions]['+dfid+'][]',
													value:option,
												})
												.prop('checked',!excludedOptions.hasOwnProperty(dfid) || excludedOptions[dfid].indexOf(option) == -1)
										)
										.append(
											$('<span>').text(datafields[dfid].description+': '+option).css({'margin-left':'0.5em'})
										)
								);
							});
						}
					}
				});
				if(dfCheckboxes.children().length) {
					e.append($('<div class="form-group">')
						.append($('<label>').text('<?= getLL('subscription_fields_edit_show_datafields') ?>'))
						.append(dfCheckboxes)
					);
				} else {
					e.append($('<p>').text('<?= getLL('subscription_fields_edit_no_datafields') ?>'));
				}
				if(optionCheckboxes.children().length) {
					e.append($('<div class="form-group">')
						.append($('<label>').text('<?= getLL('subscription_fields_edit_show_options') ?>'))
						.append(optionCheckboxes)
					);
				}
			} else {
				e.append($('<div class="form-group">')
					.append($('<label>').text('<?= getLL('subscription_fields_edit_label') ?>'))
					.append($('<input>')
						.addClass('form-control')
						.attr('name','<?= $inputName ?>['+field+'][label]')
						.val(values && values.hasOwnProperty('label') ? values.label : label)
					));
				if(!field.match(/^g[0-9]{6}(:r[0-9]{6})?$/)) {
					var mandatoryInput = createCheckbox('<?= $inputName ?>['+field+'][mandatory]','<?= getLL('subscription_fields_edit_mandatory') ?>',values && values.hasOwnProperty('mandatory') ? values.mandatory : false).addClass('mandatoryInputGroup');
					e.append(mandatoryInput);
					if(data.hasOwnProperty('type') && data.type == 'textplus') {
						e.append(createCheckbox('<?= $inputName ?>['+field+'][renderAsInput]','<?= getLL('subscription_render_textplus_as_input_label') ?>',values && values.hasOwnProperty('renderAsInput') ? values.renderAsInput : false));
						e.append($('<div class="form-group">')
							.append($('<label>')
								.text('<?= getLL('subscription_textplus_options_label') ?> ')
								.append($('<a>').append($('<span>').addClass('glyphicon glyphicon-info-sign')).tooltip({
									html:true,
									title:'<?= '<ul style="padding-left:15px;"><li style="list-style:disc;">'.preg_replace('/[\r\n]+/','</li><li style="list-style:disc;">',getLL('subscription_textplus_options_help')).'</li></ul>' ?>'.replace('%s',data.textplusOptions.map(function(e){return e == '' ? '[<?= getLL('empty') ?>]' : e;}).join(', '))
								}))
							)
							.append($('<textarea>')
								.addClass('form-control')
								.attr('name','<?= $inputName ?>['+field+'][options]')
								.text(values && values.hasOwnProperty('options') ? values.options : '')
								.css({resize:'vertical'})
							)
						);
					}
				}
				if(field.match(/^g[0-9]{6}:d[0-9]{6}$/)) {
					dfid = field.substr(9,6);
					if(datafields.hasOwnProperty(dfid) && datafields[dfid].type == 'select' || datafields[dfid].type == 'multiselect') {
						var optionCheckboxes = $('<div>');
						var excludedOptions = values && values.hasOwnProperty('excludeOptions') ? values.excludeOptions : [];
						datafields[dfid].options.forEach(function(option) {
							optionCheckboxes.append(
								$('<div>')
									.append(
										$('<input>')
											.addClass('form-control')
											.attr({
												type:'checkbox',
												name:'<?= $inputName ?>['+field+'][includeOptions][]',
												value:option,
											})
											.prop('checked',excludedOptions.indexOf(option) == -1)
									)
									.append(
										$('<span>').text(option).css({'margin-left':'0.5em'})
									)
							);
						});
						e.append($('<div class="form-group">')
							.append($('<label>').text('<?= getLL('subscription_fields_edit_show_options') ?>'))
							.append(optionCheckboxes)
						);
					}
				}

				e.append($('<div class="form-group">')
					.append($('<label>').text('<?= getLL('subscription_fields_edit_placeholder') ?>'))
					.append($('<div class="form-control">').text('###'+field+'###'))
				);
			}
			e.append($('<div class="form-group">')
				.append($('<label>').text('<?= getLL('subscription_fields_edit_help_text') ?> '))
				.append($('<textarea style="resize:vertical;">')
					.addClass('form-control')
					.attr('name','<?= $inputName ?>['+field+'][help]')
					.val(values && values.hasOwnProperty('help') ? values.help : '')
				)
			);
			e.append(this.createCheckbox('<?= $inputName ?>['+field+'][noEdit]','<?= getLL('subscription_fields_edit_no_edit') ?>',values && values.hasOwnProperty('noEdit') ? values.noEdit : false));
		}
		return e;
	}

	this.createCheckbox = function(name,label,checked) {
		return $('<div class="form-group">')
			.append($('<label>').text(label).css('display','block'))
			.append($('<input type="checkbox" name="'+name+'" value="1"'+(checked ? ' checked="checked"' : '')+'>'));
	}

	$('#subscriptionFormLayout').sortable({
		over:function() {
			removeIntent = false;
			$('#subscriptionFormLayout').sortable('option','revert',200);
		},
		out:function() {
			removeIntent = true;
			$('#subscriptionFormLayout').sortable('option','revert',false);
		},
		beforeStop:function(event,ui) {
			if(removeIntent) {
				var field = ui.item.data('field');
				ui.item.remove();
				ui.helper.remove();
				var target = $('#subscriptionFormFieldSources .formFieldSource [data-field="'+field+'"]');
				target.show();
				target.draggable('enable');
				target.removeClass('disabled');
				if(getGroupId(field)) {
					updateDatafields();
				}
			}
			$('textarea.rte',ui.item).each(function() {
				var editor = CKEDITOR.instances[this.name];
				if(editor) editor.destroy();
			});
		},
		stop:function(event,ui) {
			ui.item.width('').height('');
		},
		handle:'.panel-heading',
	});

	$('#subscriptionFormFieldSources .formFieldSource .list-group-item').each(function() {
		initDraggable($(this));
	});

	$('#subscriptionFormGroups .list-group-item').click(function() {
		$('.glyphicon',this).toggleClass('glyphicon-chevron-right').toggleClass('glyphicon-chevron-down');
	});

	$(function() {
		var isDragging = false;
		$('#subscriptionFormLayout').on('mousedown','.panel-heading',function() {
			isDragging = false;
		});
		$('#subscriptionFormLayout').on('sortstart',function() {
			isDragging = true;
		});
		$('#subscriptionFormLayout').on('mouseup','.panel-heading',function() {
			if(!isDragging) {
				$(this).siblings('.panel-body').slideToggle(200);
			}
		});
	});

	$('#subscriptionFormLayout').on('keypress','input',function(event) {
		if(event.keyCode == 13) {
			$(this).blur();
			return false;
		}
	});

	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {

			mutation.addedNodes.forEach(function(e) {
				if(!$(e).hasClass('ui-draggable-dragging') && !$(e).hasClass('ui-sortable-helper')) {

					$('textarea.rte',e).ckeditor({
						toolbar:[
							{name:'basicstyles',items:['Bold','Italic']},
							{name:'links',items:['Link','Unlink']}
						],
						height:'8em'
					});
				}
			});
		});
	});
	$('#subscriptionFormLayout').each((i,e) => observer.observe(e,{childList:true}));

	groupSelectInput.on('groupsearch.add groupsearch.remove',updateDatafields);
	updateDatafields();

	(function() {
		var datafields = [];
		var lastElem = null;
		for(field in fields) {
			if(fields.hasOwnProperty(field)) {
				var sel = field;
				var m = field.match(/^_(text|caption|hr|check)/);
				if(m) sel = m[0];
				if(sel.match(/^g[0-9]{6}:d/)) {
					lastElem = datafields.push({sel:sel,after:lastElem})-1;
				} else {
					var src = $('.formFieldSource [data-field="'+sel+'"]');
					if(src.length) {
						var elem = createFormElement(src,fields[field]);
						elem.find('.panel-body').hide();
						elem.width('').height('');
						$('#subscriptionFormLayout').append(elem);
						lastElem = elem;
						onAddToForm(elem,src);
					}
				}
			}
		}
		datafields.forEach(function(v,i) {
			var src = $('.formFieldSource [data-field="'+v.sel+'"]');
			if(src.length) {
				var elem = createFormElement(src,fields[v.sel]);
				elem.find('.panel-body').hide();
				elem.width('').height('');
				if(v.hasOwnProperty('after') && v.after !== null) {
					if((typeof v.after) === "number") {
						datafields[v.after].after(elem);
					} else {
						v.after.after(elem);
					}
				} else {
					$('#subscriptionFormLayout').prepend(elem);
				}
				datafields[i] = elem;
				onAddToForm(elem,src);
			}
		});
	})();
}

$(document).ready(function() {
	SubcriptionFormFieldSelect()
});

</script>
