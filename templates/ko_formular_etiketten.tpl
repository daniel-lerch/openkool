<h3>{$label_title}
	{if $help.show}
		<span>{$help.link}</span>
	{/if}
</h3>

{if $tpl_export_warning}
	<div class="alert alert-danger" role="alert" id="leute-warning-export">{$tpl_export_warning}</div>
{/if}

{assign var="col_size_left" value=3}
{math equation="x-y" x=12 y=$col_size_left assign="col_size_right"}

<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
					{ll key="tracking_export_layout"}
				</h4>
			</div>
			<div class="panel-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-{$col_size_left} control-label">
							{$label_preset}:
						</label>
						<div class="col-sm-{$col_size_right}">
							<select name="sel_vorlage" class="input-sm form-control">
								{foreach from=$vorlagen.values item=v key=k}
									<option value="{$v}" {if $v == $vorlagen.value}selected="selected"{/if}>{$vorlagen.output.$k}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-{$col_size_left} control-label">
							{$label_start}:
						</label>
						<div class="col-sm-{$col_size_right}">
							<input type="text" name="txt_start" class="input-sm form-control" value="1">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-{$col_size_left} control-label">
							{$label_border}:
						</label>
						<div class="col-sm-{$col_size_right}">
							<input type="radio" name="rd_rahmen" value="ja">{$label_yes}
							&nbsp;&nbsp;
							<input type="radio" name="rd_rahmen" value="nein" checked="checked">{$label_no}
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-{$col_size_left} control-label">
							{$label_fill_page}:
						</label>
						<div class="col-sm-{$col_size_right}">
							<div class="row">
								<div class="col-xs-2">
									<div class="checkbox">
										<input type="checkbox" name="chk_fill_page" onclick="if(this.checked) document.formular.txt_fill_page.style.visibility = 'visible'; else document.formular.txt_fill_page.style.visibility = 'hidden';">
										&nbsp;
									</div>
								</div>
								<div class="col-xs-10">
									<input type="text" name="txt_fill_page" value="1" class="input-sm form-control" style="visibility:hidden;">
								</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-{$col_size_left} control-label">
							{$label_multiplyer}:
						</label>
						<div class="col-sm-{$col_size_right}">
							<input type="text" name="txt_multiply" class="input-sm form-control" value="1">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-{$col_size_left} control-label">
							{$label_return_address}:
						</label>
						<div class="col-sm-{$col_size_right}">
							<div class="checkbox">
								<input type="checkbox" name="chk_return_address" {if $return_address_chk}checked="checked"{/if}>
							</div>
						</div>
					</div>
					<div id="extended_return_address" class="form-group" style="{if !$return_address_chk}display:none;{/if}">
						<div class="col-sm-{$col_size_right} col-sm-offset-{$col_size_left}">
							<select name="sel_return_address" class="input-sm form-control">
								{if $return_address_info}<option value="info_address" {if $return_address_sel == "info_address"}selected="selected"{/if}>{$return_address_info}</option>{/if}
								{if $return_address_login}<option value="login_address" {if $return_address_sel == "login_address"}selected="selected"{/if}>{$return_address_login}</option>{/if}
								<option value="manual_address" {if $return_address_sel == "manual_address"}selected="selected"{/if}>{ll key="leute_labels_manual_address"}</option>
							</select>
						</div>
						<div class="col-sm-{$col_size_right} col-sm-offset-{$col_size_left}">
							<input id="manual_return_address" type="text" class="input-sm form-control" name="txt_return_address" placeholder="{ll key="leute_labels_manual_address_placeholder"}" value="{$return_address_txt}" style="{if $return_address_sel != "manual_address"} display:none;{/if}">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-{$col_size_left} control-label">
							{$label_pp}:
						</label>
						<div class="col-sm-{$col_size_right}">
							<div class="checkbox">
								<input type="checkbox" name="chk_pp" {if $pp_chk}checked="checked"{/if}>
							</div>
						</div>
					</div>
					<div id="extended_pp" class="form-group" style="{if !$pp_chk}display:none;{/if}">
						<div class="col-sm-{$col_size_right} col-sm-offset-{$col_size_left}">
							<select name="sel_pp" class="input-sm form-control">
								{foreach from=$pp_choices item="pp_address"}
									<option value="{$pp_address}"{if $pp_sel == $pp_address} selected="selected"{/if}>{$pp_address}</option>
								{/foreach}
								<option value="manual_address" {if $pp_sel == "manual_address"}selected="selected"{/if}>{ll key="leute_labels_manual_address"}</option>
							</select>
						</div>
						<div class="col-sm-{$col_size_right} col-sm-offset-{$col_size_left}">
							<input id="manual_pp" type="text" class="input-sm form-control" name="txt_pp" placeholder="{ll key="leute_labels_manual_address_placeholder"}" value="{$pp_txt}" style="{if $pp_sel != "manual_address"} display:none;{/if}">
						</div>
						<label class="col-sm-{$col_size_left} control-label">
							{$label_priority}:
						</label>
						<div class="col-sm-{$col_size_right}">
							<div class="checkbox">
								<input type="checkbox" name="chk_priority" {if $priority_chk}checked="checked"{/if}>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		{if $settings_force_family_firstname}
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						{ll key="leute_export_etiketten_general"}
					</h4>
				</div>
				<div class="panel-body">
					<div class="formular_header">
						<label>{$settings_force_family_firstname.desc}</label>
					</div>
					<div class="formular_content">
						{assign var="input" value=$settings_force_family_firstname}
						{include file="$ko_path/templates/ko_formular_elements.tmpl"}
					</div>
				</div>
			</div>
		{/if}
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
					{$label_limiter}
				</h4>
			</div>
			<ul class="list-group">
				{assign var='first_row' value=true}
				{assign var='last_col' value=''}
				{assign var='line_counter' value=0}
				{foreach from=$tpl_cols item=col}
					<li class="list-group-item{if $col.type == 'empty' && !$first_row} empty-line-element{/if}" data-row-id="{$line_counter}">
						<div class="row">
							<div class="col-sm-1">
								{if $first_row || $col.type != 'empty'}
									<a class="btn btn-sm btn-success show_button empty-line-element" data-row-id="{$line_counter}"><i class="fa fa-plus icon-line-height"></i></a>
								{/if}
								{if $col.type == 'empty'}
									<a class="btn btn-sm btn-danger hide_button empty-line-element" data-row-id="{$line_counter}"><i class="fa fa-minus icon-line-height"></i></a>
								{/if}
							</div>
							<div class="col-sm-6">
								{if $col.type == 'empty'}
									<input type="text" class="input-sm form-control empty-line-element" data-row-id="{$line_counter}" name="{$col.name}">
								{else}
									<label class="address_line_label">{$col.name}</label>
								{/if}
							</div>
							<div class="col-sm-5">
								{if $col.show_select}
									<select name="sel_col_{$col.id}" class="input-sm form-control {if $col.type == 'empty'}empty-line-element{/if}"{if $col.type == 'empty'} data-row-id="{$line_counter}"{/if}>
										<option value="Zeilenumbruch">{$label_limiter_newline}</option>
										<option value="Doppelter Zeilenumbruch">{$label_limiter_doublenewline}</option>
										{if $col.id == 'vorname' && $last_col != 'nachname'}
											<option value="Leerschlag" selected="selected">{$label_limiter_space}</option>
										{elseif $col.id == 'nachname' && $last_col != 'vorname'}
											<option value="Leerschlag" selected="selected">{$label_limiter_space}</option>
										{elseif $col.id == "plz"}
											<option value="Leerschlag" selected="selected">{$label_limiter_space}</option>
										{else}
											<option value="Leerschlag">{$label_limiter_space}</option>
										{/if}
										<option value="Nichts">{$label_limiter_nothing}</option>
									</select>
								{else}
									&nbsp;
								{/if}
							</div>
						</div>
					</li>
					{if $col.type != 'empty'}
						{assign var='last_col' value=$col.id}
					{else}
						{assign var='line_counter' value=$line_counter+1}
					{/if}
					{assign var='first_row' value=false}
				{/foreach}
			</ul>
		</div>
	</div>
</div>

{if $crm_contact_tpl_groups}
	{assign var="tpl_groups" value=$crm_contact_tpl_groups}
	{assign var="tpl_hide_cancel" value=TRUE}
	{assign var="tpl_special_submit" value="&nbsp"}
	{include file="ko_formular.tpl"}
{/if}

<div class="btn-field">
	<button type="submit" class="btn btn-primary" value="{$label_submit}" name="submit" onclick="set_action('submit_etiketten', this);this.submit">
		<i class="fa fa-save"></i>&nbsp;{$label_submit}
	</button>
</div>

