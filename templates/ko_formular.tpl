{foreach item=hid from=$tpl_hidden_inputs}
	<input type="hidden" name="{$hid.name}" value="{$hid.value}" />
{/foreach}
{if $tpl_id}
	<input type="hidden" name="id" value="{$tpl_id}" />
{/if}

{if !$tpl_hide_header}
	<h3 class="ko_list_title">{if $help.show}<span class="pull-left help-icon">{$help.link}</span>{/if}{$tpl_titel}</h3>
{/if}

{if $tpl_export_warning}
	<div class="alert alert-danger" role="alert" id="leute-warning-export">{$tpl_export_warning}</div>
{/if}

<div class="subpart">

	{assign var="doTab" value=false}
	{assign var="firstTab" value=true}
	{foreach key=id name=groups item=group from=$tpl_groups}
		{if $group.tab || $doTab}
			{assign var="doTab" value=true}
		{else}
			{assign var="doTab" value=false}
		{/if}
		{if $doTab}
			{if $firstTab}
				<ul class="nav nav-tabs" style="font-size:1.1em" role="tablist">
			{/if}
			<li role="presentation"{if $firstTab} class="active"{/if}><a href="#tab_{$id}" aria-controls="user-settings" role="tab" data-toggle="tab">{if $group.titel}{$group.titel}{else}{ll key="kota_layout_group_backup_title"}{/if}</a></li>
			{assign var="firstTab" value=false}
		{/if}
		{assign var="doTab" value=false}
	{/foreach}
	{if !$firstTab}
		</ul>
		<div style="margin-bottom:10px;margin-top:10px;" class="tab-content">
	{/if}

	<!-- Formular-Daten -->
	{assign var="doTab" value=false}
	{assign var="firstTab2" value=true}
	{foreach key=id name=groups item=group from=$tpl_groups}
		{if $group.tab || $doTab}
			{assign var="doTab" value=true}
		{else}
			{assign var="doTab" value=false}
		{/if}
		{if $group.name}
			{assign var="groupName" value=$group.name}
		{else}
			{assign var="groupName" value=$id}
		{/if}
		{if $group.titel != ""}
			{if $group.forAll}
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="checkbox">
						<label>
							<input class="forall-checkbox" type="checkbox" name="koi[{$group.table}][doForAll]" id="koi[{$group.table}][doForAll]" onchange="forAllHeader();">
							{$group.titel}
						</label>
					</div>
						<div id="forall_group_{$groupName}" class="forall-group" style="display:none;">
			{elseif $doTab}
				{if !$firstTab2}
					</div>
				{/if}
				<div role="tabpanel" class="tab-pane{if $firstTab2} active{/if}" id="tab_{$groupName}">
				{assign var="firstTab2" value=false}
			{else}
				{if $group.appearance}
					{assign var="panelType" value=$group.appearance}
				{else}
					{assign var="panelType" value="primary"}
				{/if}
				<div class="panel panel-{$panelType}" name="group_{$groupName}" id="group_{$groupName}">
					<div class="panel-heading" role="tab" id="group_{$groupName}_heading">
						<h4 class="panel-title">
							{if $group.install_checkbox}
								<input type="checkbox" name="module_install_status[{$groupName}]"{if $group.module_installed} checked{/if} />&nbsp; &nbsp; &nbsp;{$group.titel}
							{else}
								<a style="display:block;" data-toggle="collapse" href="#group_{$groupName}_content" tabindex="-1">
									{if $group.state == "closed"}<i class="fa fa-plus"></i>{/if}&nbsp;{$group.titel}
								</a>
							{/if}
						</h4>
					</div>
					<div id="group_{$groupName}_content" class="panel-collapse collapse group-collapse{if $group.state != "closed"} in{/if}" role="tabpanel">
						<div class="panel-body">
			{/if}
		{/if}
		{assign var="rowCounter" value=0}
		{foreach name=maingroups item=maingroup from=$group.row}
			{if $maingroup.subgroup}
				<div class="row">
				{if $group.display_accesslist}

					<div class="panel panel-secondary" id="accessrights_{$groupName}_panel">
						<div class="panel-heading" role="tab" id="accessrights_{$groupName}_panel_heading">
							<div class="input-group">
								<label for="accessrights_{$groupName}">{ll key="admin_button_access_rights"}: &nbsp;</label>
								<input type="checkbox" class=" collapse_button switch" data-toggle="collapse" data-target="#accessrights_{$groupName}" aria-controls="accessrights_{$groupName}" name="accessrights_{$groupName}" data-size="small" data-off-text="nein" data-on-text="ja" value="1"{if $group.display_accesslist == "open"} checked{/if}>
							</div>
							<script>
								$('input[name="accessrights_{$groupName}"]').bootstrapSwitch({ldelim}
									onSwitchChange: function(e) {ldelim}
										$("#accessrights_{$groupName}").toggleClass('in');
										var sliders = $(this).closest(".module").find("div[id^='accessrights_']").find(".slider");
										$(sliders).each(function() {ldelim}
											var id = $(this).prop("id").substring(6);
											$("#" + id).bootstrapSlider("refresh");
										{rdelim});

										{*if($("#accessrights_{$groupName}").hasClass("in")) {ldelim}*}
											var module = $(this).closest(".module").find("input[name^='sel_rechte'][name$='_0']");
											update_all_slider(module, true);
{*										{rdelim}*}
									{rdelim}
								{rdelim});
							</script>
						</div>
						<div id="accessrights_{$groupName}" class="panel-collapse collapse group-collapse{if $group.display_accesslist == "open"} in{/if}" role="tabpanel">
							<div class="panel-body">
								<div class="card card-body container-fluid">
									{foreach name=rows item=row from=$maingroup.subgroup.rows}
										{include file="$ko_path/templates/ko_formular_sub_row.tpl"}
									{/foreach}
								</div>
							</div>
					</div>
				</div>
                {/if}
				</div>


            {else}
				{assign var="row" value=$maingroup}
				{include file="$ko_path/templates/ko_formular_sub_row.tpl"}
			{/if}
		{/foreach}
		{if $group.show_save || $group.forAll}
			<div class="btn-field">
				{if $tpl_special_submit}
					{$tpl_special_submit}
				{else}
					<button type="submit" class="btn btn-primary" name="submit" class="ko_form_submit {$submit_class}" value="{$tpl_submit_value}" onclick="var ok = check_mandatory_fields($(this).closest('form')); if (ok) {ldelim}{$tpl_onclick_action}set_action('{$tpl_action}', this){rdelim} else return false;">
						{$tpl_submit_value} <i class="fa fa-save"></i>
					</button>
				{/if}
				{if !$tpl_hide_cancel}
					<button type="submit" class="btn btn-danger" name="cancel" value="{$label_cancel}" onclick="set_action('{$tpl_cancel}', this);">
						{$label_cancel} <i class="fa fa-remove"></i>
					</button>
				{/if}
			</div>
			<p>
				{if $tpl_submit_as_new && !$force_hide_submit_as_new}
					<br />
					<button type="submit" class="btn btn-success" name="submit_as_new" value="{$tpl_submit_as_new}" onclick="set_action('{$tpl_action_as_new}', this);">
						{$tpl_submit_as_new} <i class="fa fa-plus"></i>
					</button>
				{/if}
			</p>
		{/if}
		{if $group.titel != ""}
			{if !$group.forAll}
				{if !$doTab}
							</div>
						</div>
					</div>
				{/if}
			{else}
						</div>
					</div>
				</div>
			{/if}
		{/if}
		{assign var="doTab" value=false}
	{/foreach}

	{if !$firstTab2}
		</div>
	{/if}

	{if !$firstTab}
		</div>
	{/if}
</div>

<div class="btn-field">
{if $tpl_special_submit}
	{$tpl_special_submit}
{else}
	<button type="submit" class="btn btn-primary" name="submit" class="ko_form_submit {$submit_class}" value="{$tpl_submit_value}" onclick="var ok = check_mandatory_fields($(this).closest('form')); if (ok) {ldelim} disable_onunloadcheck(); {$tpl_onclick_action}set_action('{$tpl_action}', this){rdelim} else return false;">
		{$tpl_submit_value} <i class="fa fa-save"></i>
	</button>
{/if}
{if !$tpl_hide_cancel}
	<button type="submit" class="btn btn-danger" name="cancel" value="{$label_cancel}" onclick="disable_onunloadcheck(); set_action('{$tpl_cancel}', this);">
		{$label_cancel} <i class="fa fa-remove"></i>
	</button>
{/if}
{if $tpl_submit_as_new && !$force_hide_submit_as_new}
	<br />
	<button type="submit" class="btn btn-success" name="submit_as_new" value="{$tpl_submit_as_new}" onclick="var ok = check_mandatory_fields($(this).closest('form')); if (ok) {ldelim} disable_onunloadcheck(); set_action('{$tpl_action_as_new}', this); {rdelim} else return false;">
		{$tpl_submit_as_new} <i class="fa fa-plus"></i>
	</button>
{/if}
{if $additional_button}
	{$additional_button}
{/if}
</div>

{if $tpl_legend}
	<div style="margin-top: 10px; color: #666;">
		{if $tpl_legend_icon}<img src="{$ko_path}images/{$tpl_legend_icon}" alt="legend" border="0" align="left" />&nbsp;{/if}
		{$tpl_legend}
	</div>
{/if}
