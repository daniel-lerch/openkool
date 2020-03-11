{foreach item=hid from=$tpl_hidden_inputs}
	<input type="hidden" name="{$hid.name}" value="{$hid.value}" />
{/foreach}
{if $tpl_id}
	<input type="hidden" name="id" value="{$tpl_id}" />
{/if}

{if !$tpl_hide_header}
	<h3 class="ko_list_title">
		{if $help.show}<span class="pull-left help-icon">{$help.link}</span>{/if}
		{$tpl_titel}
	</h3>
{/if}

<div class="subpart">
	{preprocessForm form=$tpl_groups}

	{assign var="doTabs" value=false}
	{if $tpl_groups|@count > 1}
		{assign var="doTabs" value=true}
		<ul class="nav nav-tabs" style="font-size:1.1em" role="tablist">
			{foreach key=tabKey name=tabs item=tab from=$tpl_groups}
				<li role="presentation" class="{if $tab.active}active{/if}"><a href="#tab_{$tab.name}" aria-controls="user-settings" role="tab" data-toggle="tab">{$tab.titel}</a></li>
			{/foreach}
		</ul>
		<div style="margin-bottom:10px;margin-top:10px;" class="tab-content">
	{/if}

	{foreach key=tabKey name=tabs item=tab from=$tpl_groups}
		{assign var="showSave" value=1}
		{if $doTabs}
			<div role="tabpanel" class="tab-pane{if $tab.active} active{/if}" id="tab_{$tab.name}">
		{/if}
		{foreach key=groupKey name=groups item=group from=$tab.groups}
			{if $group.group}
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

				{else}
					<div class="panel panel-{$group.appearance}" name="group_{$group.name}" id="group_{$group.name}">
						<div class="panel-heading" role="tab" id="group_{$group.name}_heading">
							{if $group.options}
								{if $group.options.delete_action}
									<button type="submit" class="header-btn__big btn-danger" data-action="{$group.options.delete_action}" data-message="{ll key="list_label_confirm_delete"}" title="{ll key="form_ft_button_delete_title"}"
										onclick="if(!delete_form_item(this)) return false;"><i class="fa fa-trash"></i>
									</button>
								{/if}
							{/if}
							<h4 class="panel-title">
								<a style="display:block;" data-toggle="collapse" href="#group_{$group.name}_content" tabindex="-1">
									{if $group.state == "closed"}<i class="fa fa-plus"></i>{/if}&nbsp;{$group.titel}
								</a>
							</h4>
						</div>
						<div id="group_{$group.name}_content" class="panel-collapse collapse group-collapse{if $group.state != "closed"} in{/if}" role="tabpanel">
							<div class="panel-body">
				{/if}
			{/if}
			{foreach key=rowKey name=rows item=row from=$group.rows}
				<div class="row">
					{foreach key=inputKey name=inputs item=input from=$row.inputs}
						<div class="formular-cell col-md-{$input.columnWidth} col-sm-{math equation="x / 2" x=$bootstrap_cols_per_row}">
							<div class="formular_header{if $input.headerclass} {$input.headerclass}{/if}">
								{if $input.descicon}
									{if $input.descicon.icon}
										{assign var="desciconContext" value=""}
										{if $input.descicon.context}
											{assign var="desciconContext" value=" text-`$input.descicon.context`"}
										{/if}
										<i class="fa fa-{$input.descicon.icon}{$desciconContext}"></i>
									{else}
										<i class="fa fa-{$input.descicon}"></i>
									{/if}
									&nbsp;
								{/if}
								{if $input.descimg}<img src="{$ko_path}images/{$input.descimg}" border="0" />{/if}
								{if $input.title_pre_html}{$input.title_pre_html}{/if}
								<label for="{$input.name}">{$input.desc}{if $input.is_mandatory} *{/if}</label>{if $input.help}&nbsp;{$input.help}{/if}
							</div>
							<div class="formular_content{if $input.contentclass} {$input.contentclass}{/if}">
								{if $input.type == "_save"}
									{if $showSave}
										<div class="btn-field">
											{if $tpl_special_submit}
												{$tpl_special_submit}
											{else}
												<button type="submit" class="btn btn-primary" name="submit" class="ko_form_submit {$submit_class}" value="{$tpl_submit_value}" onclick="disable_onunloadcheck();{$tpl_onclick_action}set_action('{$tpl_action}', this)">
													{$tpl_submit_value} <i class="fa fa-save"></i>
												</button>
											{/if}
											{if !$tpl_hide_cancel}
												<button type="submit" class="btn btn-danger" name="cancel" value="{$label_cancel}" onclick="disable_onunloadcheck();set_action('{$tpl_cancel}', this);">
													{$label_cancel} <i class="fa fa-remove"></i>
												</button>
											{/if}
											{if $tpl_submit_as_new && !$force_hide_submit_as_new}
												<br />
												<button type="submit" class="btn btn-success" name="submit_as_new" value="{$tpl_submit_as_new}" onclick="disable_onunloadcheck();set_action('{$tpl_action_as_new}', this);">
													{$tpl_submit_as_new} <i class="fa fa-plus"></i>
												</button>
											{/if}
										</div>
										{assign var="showSave" value=0}
									{/if}
								{elseif $input.type == "_sep"}
									<p></p>
								{else}
									{include file="$ko_path/templates/ko_formular_elements.tmpl"}
									{assign var="showSave" value=1}
								{/if}
							</div>
						</div>
					{/foreach}
				</div>
			{/foreach}

			{if $group.show_save || $group.forAll}
				<div class="btn-field">
					{if $tpl_special_submit}
						{$tpl_special_submit}
					{else}
						<button type="submit" class="btn btn-primary" name="submit" class="ko_form_submit {$submit_class}" value="{$tpl_submit_value}" onclick="var ok = check_mandatory_fields($(this).closest('form')); if (ok) {ldelim}disable_onunloadcheck();{$tpl_onclick_action}set_action('{$tpl_action}', this){rdelim} else return false;">
							{$tpl_submit_value} <i class="fa fa-save"></i>
						</button>
					{/if}
					{if !$tpl_hide_cancel}
						<button type="submit" class="btn btn-danger" name="cancel" value="{$label_cancel}" onclick="disable_onunloadcheck();set_action('{$tpl_cancel}', this);">
							{$label_cancel} <i class="fa fa-remove"></i>
						</button>
					{/if}
				</div>
			{/if}

			{if $group.group}
				{if $group.forAll}
								</div>
							</div>
						</div>
				{else}
								</div>
							</div>
						</div>
				{/if}
			{/if}
		{/foreach}
		{if $doTabs}
			</div>
		{/if}
	{/foreach}

	{if $doTabs}
		</div>
	{/if}

</div>


{if $doTabs || $showSave}
	<div class="btn-field">
		{if $tpl_special_submit}
			{$tpl_special_submit}
		{else}
			<button type="submit" class="btn btn-primary" name="submit" class="ko_form_submit {$submit_class}" value="{$tpl_submit_value}" onclick="var ok = check_mandatory_fields($(this).closest('form')); if (ok) {ldelim}disable_onunloadcheck();{$tpl_onclick_action}set_action('{$tpl_action}', this){rdelim} else return false;">
				{$tpl_submit_value} <i class="fa fa-save"></i>
			</button>
		{/if}
		{if !$tpl_hide_cancel}
			<button type="submit" class="btn btn-danger" name="cancel" value="{$label_cancel}" onclick="disable_onunloadcheck();set_action('{$tpl_cancel}', this);">
				{$label_cancel} <i class="fa fa-remove"></i>
			</button>
		{/if}
		{if $tpl_submit_as_new && !$force_hide_submit_as_new}
			<br />
			<button type="submit" class="btn btn-success" name="submit_as_new" value="{$tpl_submit_as_new}" onclick="var ok = check_mandatory_fields($(this).closest('form')); if (ok) {ldelim}disable_onunloadcheck();set_action('{$tpl_action_as_new}', this);{rdelim} else return false;">
				{$tpl_submit_as_new} <i class="fa fa-plus"></i>
			</button>
		{/if}
		{if $additional_button}
			{$additional_button}
		{/if}
	</div>
{/if}


{if $tpl_crinfo}
	<br />
	<div class="well well-small">
		<h4>{ll key="crinfo_title"}</h4>
		{$tpl_crinfo}
	</div>
{/if}


{if $tpl_legend}
	<div style="margin-top: 10px; color: #666;">
		{if $tpl_legend_icon}<img src="{$ko_path}images/{$tpl_legend_icon}" alt="legend" border="0" align="left" />&nbsp;{/if}
		{$tpl_legend}
	</div>
{/if}
