<input type="hidden" value="{$tpl_id}" name="leute_id" />
<input type="hidden" value="{$tpl_nl_id}" name="add_to_nl" />
<input type="hidden" value="{$tpl_nl_mod_id}" name="nl_mod_id" />
<input type="hidden" name="hid_new_family" value="0" />

<h3>{$tpl_titel}</h3>

<!-- family data -->
{if !$hide_fam}
	<div class="row">
		<div class="col-sm-4 formular-cell">
			<label>{$label_spouse}</label>
			<div class="family-relative">
				<input type="hidden" name="input_spouse" class="family-relative-id"{if $family.spouse} data-avalues="{$family.spouse.id}" data-adescs="{$family.spouse.name}" data-atitles="{$family.spouse.title}"{/if}{if $fam_readonly} data-disabled="1"{/if}>
			</div>
		</div>
		<div class="col-sm-4 formular-cell">
			<label>{$label_father}</label>
			<div class="family-relative">
				<input type="hidden" name="input_father" class="family-relative-id"{if $family.father} data-avalues="{$family.father.id}" data-adescs="{$family.father.name}" data-atitles="{$family.father.title}"{/if}{if $fam_readonly} data-disabled="1"{/if}>
			</div>
		</div>
		<div class="col-sm-4 formular-cell">
			<label>{$label_mother}</label>
			<div class="family-relative">
				<input type="hidden" name="input_mother" class="family-relative-id"{if $family.mother} data-avalues="{$family.mother.id}" data-adescs="{$family.mother.name}" data-atitles="{$family.mother.title}"{/if}{if $fam_readonly} data-disabled="1"{/if}>
			</div>
		</div>
	</div>
		<div class="row">
			<div class="col-md-8 col-lg-7 formular-cell">
				<div class="formular_header">
					<label>{$label_family}</label>
				</div>
				<div class="formular_content">
					<div class="btn-toolbar">
						<div class="input-group input-group-sm">
							<div class="input-group-btn auto-width">
								<button type="button" class="btn btn-default" onclick="javascript:change_vis('fam_content');"><i class="fa fa-group"></i></button>
							</div>
							<select style="max-width:220px;" name="sel_familie" id="sel_familie" class="input-sm form-control" data-text-new="{ll key="leute_labels_household_new"}" data-text-none="{ll key="leute_labels_household_none"}" {$fam_params}>
								{foreach from=$fam_sel.values item=v key=k}
									<option value="{$v}" {if $v == $fam_sel.sel}selected="selected"{/if} title="{$fam_sel.titles.$k}">{$fam_sel.descs.$k}</option>
								{/foreach}
							</select>
							<div class="input-group-btn auto-width">
								<button type="button" name="submit_neue_fam" class="btn btn-success" title="{$label_family_new}" value="{$label_family_new}" onclick="$('[name=&quot;hid_new_family&quot;]').val('1').change();clear_family();"{if $fam_readonly} disabled{/if}>
									{$label_family_new} <i class="fa fa-group icon-line-height"></i>
								</button>
							</div>
						</div>
						<div class="input-group input-group-sm">
							<div class="input-group-btn auto-width">
								<button class="btn btn-default" disabled>{$label_familyrole}</button>
							</div>
							<select name="input_famfunction" class="input-sm form-control" {$fam_params}>
								{foreach from=$famfunction.values item=v key=k}
									<option value="{$v}" {if $v == $famfunction.sel}selected="selected"{/if}>{$famfunction.descs.$k}</option>
								{/foreach}
							</select>
						</div>
						<div class="btn-group btn-group-sm">
							<button type="submit" name="submit_fam" class="btn btn-primary" title="{$label_ok}" value="{$label_ok}" onclick="set_action('{$tpl_action}', this)"{if $fam_readonly} disabled{/if}>
								<i class="fa fa-save"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-lg-5 formular-cell" id="household-suggestions-container" style="display:none;">
				<div class="formular_header formular-header-primary">
					<label>{ll key="leute_labels_add_to_household"}</label>
				</div>
				<div class="formular_content">
					<div id="household-suggestions"></div>
				</div>
			</div>
		</div>

		<div id="fam_content" class="well" style="visibility:hidden;display:none;">
			{assign var="counter" value=0}
			{foreach item=fd from=$cols_familie}
				{if $counter%2==0}<div class="row">{/if}
					<div class="formular-cell col-xs-12 col-sm-6">
						<div class="formular_header">
							<label>{$fd.desc}</label>
						</div>
						<div class="formular_content">
							{if $fd.type == "text"}
								<input type="text" class="input-sm form-control" name="{$fd.name}" value="{$fd.value}" {$fam_params}>
							{elseif $fd.type == "select"}
								<select name="{$fd.name}" class="input-sm form-control" {$fam_params}>
									{foreach from=$fd.values item=v key=k}
										<option value="{$v}" {if $v == $fd.value}selected="selected"{/if}>{$fd.descs.$k}</option>
									{/foreach}
								</select>
							{/if}
						</div>
					</div>
				{assign var="counter" value=$counter+1}
				{if $counter%2==0}</div>{/if}
			{/foreach}
			{if $counter%2==1}</div>{/if}
		</div>
	</div>
{/if}
<!-- family data end -->

<!-- Formular-Daten -->
{section name=row loop=$tpl_rows}

	<div class="row">
		{foreach name=inputs item=input from=$tpl_rows[row].inputs}
			{if $input.type == "header"}
				</div>
				<h3 class="subpart_header">{$input.value}</h3>
				<div class="row">
			{elseif $input.type == "   "}
				</div>
				<br>
				<div class="row">
			{else}
			<div class="formular-cell col-xs-12 col-sm-{if $input.colspan}{$input.colspan*6}{else}6{/if}{if $input.fam_feld && !$hide_fam} leute-family-field{/if}">
				<div class="{if $input.headerclass}{$input.headerclass}{else}formular_header{/if}">
					{if $input.fam_feld && !$hide_fam}<i class="fa fa-group" alt="{$label_family|truncate:1:""}" title="{$label_family}"></i>{/if}
					<label>{$input.desc}{if $input.fam_feld_warn_changes && !$hide_fam}&nbsp;&nbsp;<span class="family_field_warning" style="color:orangered;visibility:hidden">{ll key="leute_warning_family_fields"}</span>{/if}</label>
				</div>
				<div class="formular-content{if $input.fam_feld_warn_changes && !$hide_fam} family_field_with_warning{/if}">
					{if $input.type == "varchar" || $input.type == "date" || $input.type == "smallint" || $input.type == "mediumint"}
						{if $input.chk_preferred}
						<div class="input-group input-group-sm full-width">
								<span class="input-group-addon">
									{$input.chk_preferred}
								</span>
						{/if}
						<input type="text" class="input-sm form-control" name="{$input.name}" value="{$input.value}" {$input.params}>
						{if $input.chk_preferred}
						</div>
						{/if}

					{elseif $input.type == "tinyint"}
						<input type="checkbox" class="switch" name="{$input.name}" data-size="small" data-off-text="{if $input.label_0}{$input.label_0}{else}{ll key="no"}{/if}" data-on-text="{if $input.label_1}{$input.label_1}{else}{ll key="yes"}{/if}" {$input.params} value="1"{if $input.value == '1'} checked{/if}>{$input.desc2}

						<script>
							$('input[name="{$input.name}"]').bootstrapSwitch();
						</script>

					{elseif $input.type == "blob" || $input.type == "text"}
						<textarea class="input-sm form-control" name="{$input.name}" {$input.params} cols="40" rows="3">{$input.value}</textarea>

					{elseif $input.type == "enum"}
						<select name="{$input.name}" class="input-sm form-control" {$input.params}>
							{foreach key=k item=v from=$input.values}
								<option value="{$v}" {if $v == $input.value}selected="selected"{/if}>{$input.descs[$k]}</option>
							{/foreach}
						</select>

					{elseif $input.type == "doubleselect"}
						<table>
							<tr>
								<td class="v-align-t" style="width:50%;">
									<input type="hidden" class="{$input.add_class}" name="{$input.name}" value="{$input.avalue}">
									<input type="hidden" name="old_{$input.name}" value="{$input.avalue}">
									<div style="font-size:x-small;">{$label_form_ds_objects}:</div>
									<div class="doubleselect doubleselect-left" name="sel_ds1_{$input.name}" data-js-func-add="double_select_add" data-target-name="sel_ds2_{$input.name}" data-hidden-name="{$input.name}" {$input.params}>
										{foreach from=$input.values item=v key=k}
											<div class="select-item" data-value="{$v}">{$input.descs.$k}</div>
										{/foreach}
									</div>
								</td>
								<td class="v-align-t btn-column" style="width:1%;">
									<div style="font-size:x-small;">&nbsp;</div>
									<div class="btn-group-vertical">
									{if $input.show_moves}
										<button type="button" class="btn btn-xs btn-default" alt="top" title="{$label_form_ds_top}" onclick="double_select_move('{$input.name}', 'top');">
											<i class="fa fa-angle-double-up"></i>
										</button>
										<button type="button" class="btn btn-xs btn-default" alt="up" title="{$label_form_ds_up}" onclick="double_select_move('{$input.name}', 'up');">
											<i class="fa fa-angle-up"></i>
										</button>
										<button type="button" class="btn btn-xs btn-default" alt="down" title="{$label_form_ds_down}" onclick="double_select_move('{$input.name}', 'down');">
											<i class="fa fa-angle-down"></i>
										</button>
										<button type="button" class="btn btn-xs btn-default" alt="bottom" title="{$label_form_ds_bottom}" onclick="double_select_move('{$input.name}', 'bottom');">
											<i class="fa fa-angle-double-down"></i>
										</button>
										<button type="button" class="btn btn-xs btn-danger" alt="del" title="{$label_form_ds_del}" onclick="double_select_move('{$input.name}', 'del');">
											<i class="fa fa-remove"></i>
										</button>
									{else}
										<button type="button" class="btn btn-xs btn-danger" alt="del" title="{$label_doubleselect_remove}" onclick="double_select_move('{$input.name}', 'del');">
											<i class="fa fa-remove"></i>
										</button>
									{/if}
									</div>
								</td>
								<td class="v-align-t" style="width:50%;">
									<div style="font-size:x-small;">{$label_form_ds_assigned}:</div>
									<div class="doubleselect doubleselect-right" id="{$id}" name="sel_ds2_{$input.name}" {$input.params}>
										{foreach from=$input.avalues item=v key=k}
											<div class="select-item" data-value="{$v}">{$input.adescs.$k}</div>
										{/foreach}
									</div>
								</td>
							</tr>
						</table>

					{elseif $input.type == "groupselect"}
						<table>
							<tr>
								<td class="v-align-t" style="width:35%;">
									<input type="hidden" name="{$input.name}" value="{$input.avalue}" />
									<input type="hidden" name="old_{$input.name}" value="{$input.avalue}" />
									<div style="font-size:x-small;">{$label_group}:</div>
									<div class="groupselect groupselect-left" name="sel_ds0_{$input.name}" {$input.params} size="10" data-js-func-add="fill_grouproles_select" style="border-top-right-radius:0px;border-bottom-right-radius:0px;border-right:0px;"></div>
								</td>
								<td class="v-align-t" style="width:30%;">
									<div style="font-size:x-small;">{$label_grouprole}:</div>
									<div class="groupselect doubleselect-left" name="sel_ds1_{$input.name}" {$input.params} size="10" data-js-func-add="double_select_add" data-target-name="sel_ds2_{$input.name}" data-hidden-name="{$input.name}" data-js-after-add="{$input.onclick_2_add}" style="border-top-left-radius:0px;border-bottom-left-radius:0px;"></div>
								</td>
								<td class="v-align-t btn-column" style="width:1%;">
									<div style="font-size:x-small;">&nbsp;</div>
									<button type="button" class="btn btn-xs btn-danger" alt="del" title="{$label_doubleselect_remove}" onclick="{if $allow_assign}double_select_move('{$input.name}', 'del');{$input.onclick_del_add}{/if}">
										<i class="fa fa-remove"></i>
									</button>
								</td>
								<td class="v-align-t" style="width:35%;">
									<div style="font-size:x-small;">{$label_group_assigned}:</div>
									<div class="doubleselect doubleselect-right" id="{$id}" name="sel_ds2_{$input.name}" {$input.params} size="10">
										{foreach from=$input.avalues item=v key=k}
											<div class="select-item" data-value="{$v}" title="{$input.adescs.$k}">{$input.adescs.$k}</div>
										{/foreach}
									</div>
								</td>
							</tr>
						</table>

					{elseif $input.type == "file"}
						<input type="file" name="{$input.name}" {$input.params}>
						{if $input.value}<br />{$input.value}{/if}
						{if $input.value}<br /><input type="checkbox" name="{$input.name2}" value="1" />{$input.value2}{/if}

					{elseif $input.type == "_save"}
						<div class="btn-field">
							<button type="submit" class="btn btn-sm btn-primary" name="submit" value="{$label_save}" onclick="set_action('{$tpl_action}', this)">
								{$label_save} <i class="fa fa-save"></i>
							</button>
							<button type="submit" class="btn btn-sm btn-danger" name="cancel" value="{$label_cancel}" onclick="set_action('show_all', this)">
								{$label_cancel} <i class="fa fa-remove"></i>
							</button>
						</div>

					{elseif $input.type == "html"}
						{$input.value}

					{else}
						{include file="$ko_path/templates/ko_formular_elements.tmpl"}

					{/if}
				</div>
			</div>
			{/if}
		{/foreach}
	</div>

{/section}


<div class="row">
	<div class="btn-field col-xs-12 col-sm-{if $announce_values}6{else}12{/if}">
		<button type="submit" class="btn btn-primary" name="submit" value="{$label_save}" onclick="set_action('{$tpl_action}', this)">
			{$label_save} <i class="fa fa-save"></i>
		</button>
		<button type="submit" class="btn btn-danger" name="cancel" value="{$label_cancel}" onclick="set_action('show_all', this)">
			{$label_cancel} <i class="fa fa-remove"></i>
		</button>
		{if $tpl_action_neu != ""}
			<br />
			<button type="submit" class="btn btn-success" name="submit_neu" value="{$label_as_new_person}" onclick="set_action('{$tpl_action_neu}', this)">
				{$label_as_new_person} <i class="fa fa-plus"></i>
			</button>
		{/if}
	</div>
	{if $announce_values}
		<div class="text-center col-xs-12 col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h5 class="panel-title">
						<a data-toggle="collapse" href="#leute_announce_changes">
							{$label_announce_title}
						</a>
					</h5>
				</div>
				<div class="panel-collapse collapse" id="leute_announce_changes">
					<div class="panel-body">
						<div class="form-group">
							<label>
								{$label_announce_description}
							</label>
							<select class="input-sm form-control" name="sel_announce_changes[]" size="4" multiple>
								{foreach from=$announce_values item=v key=k}
									<option value="{$v}">{$announce_descs.$k}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
	{/if}
</div>


{if $tpl_legend}
	<div style="margin-top: 10px; color: #666;">
		<i class="fa fa-{$tpl_legend_icon}" alt="legend" align="left"></i>&nbsp;{$tpl_legend}
	</div>
{/if}

<script>
	var leute_warning_family_fields_changed = '{$leute_warning_family_fields_changed}';
</script>
