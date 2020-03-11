<input type="hidden" name="hid_new_family" value="0" />

<div class="row">
	<div class="col-sm-4 formular-cell">
		<label>{ll key="kota_ko_leute_spouse"}</label>
		<div class="family-relative">
			{assign var="input" value=$family.spouse.input}
			{include file="$ko_path/templates/ko_formular_elements.tmpl"}
		</div>
	</div>
	<div class="col-sm-4 formular-cell">
		<label>{ll key="kota_ko_leute_father"}</label>
		<div class="family-relative">
			{assign var="input" value=$family.father.input}
			{include file="$ko_path/templates/ko_formular_elements.tmpl"}
		</div>
	</div>
	<div class="col-sm-4 formular-cell">
		<label>{ll key="kota_ko_leute_mother"}</label>
		<div class="family-relative">
			{assign var="input" value=$family.mother.input}
			{include file="$ko_path/templates/ko_formular_elements.tmpl"}
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-8 col-lg-7 formular-cell">
		<div class="formular_header">
			<label>{ll key="form_leute_family"}
				{if $help_famid.show}{$help_famid.link}{/if}
			</label>
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
						<button type="button" name="submit_neue_fam" class="btn btn-success" title="{ll key="form_leute_family_new"}" value="{ll key="form_leute_family_new"}" onclick="$('[name=&quot;hid_new_family&quot;]').val('1').change();clear_family();"{if $fam_readonly} disabled{/if}>
							{ll key="form_leute_family_new"} <i class="fa fa-group icon-line-height"></i>
						</button>
					</div>
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-btn auto-width">
						<button class="btn btn-default" disabled>{ll key="form_leute_familyrole"}</button>
					</div>
					<select name="input_famfunction" class="input-sm form-control" {$fam_params}>
						{foreach from=$famfunction.values item=v key=k}
							<option value="{$v}" {if $v == $famfunction.sel}selected="selected"{/if}>{$famfunction.descs.$k}</option>
						{/foreach}
					</select>
				</div>
				<div class="btn-group btn-group-sm">
					<button type="submit" name="submit_fam" class="btn btn-primary" title="{ll key="OK"}" value="{ll key="OK"}" onclick="var ok = check_mandatory_fields($(this).closest('form')); if (ok) {ldelim}disable_onunloadcheck(); set_action('{$tpl_action}', this); {rdelim} else return false;"{if $fam_readonly} disabled{/if}>
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

{if count($householdmembers) > 0}
<div class="row">
	<div class="col-md-12 formular-cell">
		<div class="formular_header">
			<label>{ll key="family_all_persons"}</label>
		</div>
		<div class="formular_content">
			<table class=" table table-alternating table-bordered table-hover tablesaw tablesaw-stack" data-tablesaw-mode="stack">
				<thead>
				<tr class="row-info no-hover">
					<th class="ko_list">{ll key="kota_ko_leute_vorname"}</th>
					<th class="ko_list">{ll key="kota_ko_leute_nachname"}</th>
					<th class="ko_list">{ll key="kota_ko_leute_famfunction"}</th>
					<th class="ko_list">{ll key="kota_ko_leute_geburtsdatum"}</th>
					<th class="ko_list">{ll key="kota_ko_leute_zivilstand"}</th>
				</tr>
				</thead>
				<tbody>
				{foreach item=householdmember from=$householdmembers}
				<tr class="{cycle values="row-even, row-odd"}{if $l.rowclass} {$l.rowclass}{/if}">
					<td>{$householdmember.vorname}</td>
					<td>{$householdmember.nachname}</td>
					<td>{$householdmember.famfunction}</td>
					<td>{$householdmember.geburtsdatum}</td>
					<td>{$householdmember.zivilstand}</td>
				</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
{/if}

<script>
	var leute_warning_family_fields_changed = '{ll key="leute_warning_family_fields_changed"}';
</script>
