<input type="hidden" value="{$tpl_id}" name="leute_id" />
<input type="hidden" value="{$tpl_nl_id}" name="add_to_nl" />
<input type="hidden" value="{$tpl_nl_mod_id}" name="nl_mod_id" />
<input type="hidden" name="hid_new_family" value="0" />

<table width="100%" cellspacing="0"><tr><td class="subpart_header">
{$tpl_titel}
</td><td align="right">
&nbsp;
</td></tr>
                                                                                                                    
<tr><td class="subpart" colspan="2">

<table border="0" width="100%" cellspacing="0" cellpadding="0">

<!-- family data -->
{if !$hide_fam}
	{if $fam}
		<tr><td class="formular_header" colspan="2">
		<a href="#" onclick="javascript:change_vis('fam_content');change_fam_image('{$ko_path}');">
		<img src="{$ko_path}images/icon_arrow_down_big_disabled.png" border="0" alt="expand" id="fam_plus_image" />
		<img src="{$ko_path}images/icon_familie.png" border="0" />
		{$label_family}: {$fam_id}
		</a>
		&nbsp;&nbsp;
		<input type="button" name="submit_neue_fam" value="{$label_family_new}" onclick="set_hidden_value('hid_new_family', '1', this);set_vis('fam_content');" {$fam_params}>
		&nbsp;&nbsp;&nbsp;&nbsp;
		&nbsp;{$label_familyrole}:&nbsp;
		<select name="input_famfunction" size="0" {$fam_params}>
		{foreach from=$famfunction.values item=v key=k}
			<option value="{$v}" {if $v == $famfunction.sel}selected="selected"{/if}>{$famfunction.descs.$k}</option>
		{/foreach}
		</select>
		</td></tr>

		<tr><td class="formular_content" style="border-left:1px grey solid;border-right:1px grey solid;border-bottom:1px grey solid;" colspan="2">
		<div id="fam_content" style="visibility:hidden;display:none;">
			<select name="sel_familie" size="0" {$fam_params}>
			{foreach from=$fam_sel.values item=v key=k}
				<option value="{$v}" {if $v == $fam_sel.sel}selected="selected"{/if}>{$fam_sel.descs.$k}</option>
			{/foreach}
			</select>
			&nbsp;&nbsp;
			<input type="submit" name="submit_fam" value="{$label_ok}" onclick="set_action('{$tpl_action}', this)" {$fam_params} />
			<br />
			<table border="0" cellspacing="0" cellpadding="0">
			{foreach item=fd from=$cols_familie}
				<tr><td class="formular_header">{$fd.desc}</td></tr>
				<tr><td class="formular_content">
				{if $fd.type == "text"}
					<input type="text" name="{$fd.name}" value="{$fd.value}" size="40" {$fam_params} />
				{elseif $fd.type == "select"}
					<select name="{$fd.name}" size="0" {$fam_params}>
					{foreach from=$fd.values item=v key=k}
						<option value="{$v}" {if $v == $fd.value}selected="selected"{/if}>{$fd.descs.$k}</option>
					{/foreach}
					</select>
				{/if}
				</td></tr>
			{/foreach}
			</table>
		</div>
		</td></tr>

	{else}
		<tr><td class="formular_header" colspan="2">
		{$label_family}:
		&nbsp;&nbsp;
		<select name="sel_familie" size="0" {$fam_params}>
		{foreach from=$fam_sel.values item=v key=k}
			<option value="{$v}" {if $v == $fam_sel.sel}selected="selected"{/if}>{$fam_sel.descs.$k}</option>
		{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		&nbsp;{$label_familyrole}:&nbsp;
		<select name="input_famfunction" size="0" {$fam_params}>
		{foreach from=$famfunction.values item=v key=k}
			<option value="{$v}" {if $v == $famfunction.sel}selected="selected"{/if}>{$famfunction.descs.$k}</option>
		{/foreach}
		</select>
		&nbsp;&nbsp;
		<input type="submit" name="submit_fam" value="{$label_ok}" onclick="set_action('{$tpl_action}', this)" {$fam_params} />
		&nbsp;&nbsp;
		<input type="button" name="submit_neue_fam" value="{$label_family_new}" onclick="set_hidden_value('hid_new_family', '1', this);set_vis('fam_content');" {$fam_params} />
		</td></tr>

		<tr><td class="formular_content" style="border-left:1px grey solid;border-right:1px grey solid;border-bottom:1px grey solid;" colspan="2">
		<div id="fam_content" style="visibility:hidden;display:none;">
			<table border="0" cellspacing="0" cellpadding="0">
			{foreach item=fd from=$cols_familie}
				<tr><td class="formular_header">{$fd.desc}</td></tr>
				<tr><td class="formular_content">
				{if $fd.type == "text"}
					<input type="text" name="{$fd.name}" value="{$fd.value}" size="40" {$fam_params} />
				{elseif $fd.type == "select"}
					<select name="{$fd.name}" size="0" {$fam_params}>
					{foreach from=$fd.values item=v key=k}
						<option value="{$v}" {if $v == $fd.value}selected="selected"{/if}>{$fd.descs.$k}</option>
					{/foreach}
					</select>
				{/if}
				</td></tr>
			{/foreach}
			</table>
		</div>
		</td></tr>
	{/if}
	<tr><td><br /></td></tr>
{/if}
<!-- family data end -->

<!-- Formular-Daten -->
{section name=row loop=$tpl_rows}

	<tr>
	{foreach name=inputs item=input from=$tpl_rows[row].inputs}
		<td class="{if $input.headerclass}{$input.headerclass}{else}formular_header{/if}" {$input.colspan}>
		{if $input.fam_feld}<img src="{$ko_path}images/icon_familie.png" alt="{$label_family|truncate:1:""}" title="{$label_family}" />{/if}
		{$input.desc}{if $input.fam_feld_warn_changes}   <span class="family_field_warning" style="color:orangered;visibility:hidden">{ll key="leute_warning_family_fields"}</span>{/if}
		</td>
	{/foreach}
	</tr>

	<tr>
	{foreach name=inputs item=input from=$tpl_rows[row].inputs}

		{if $input.type == "varchar" || $input.type == "date" || $input.type == "smallint" || $input.type == "mediumint"}
			<td class="formular_content {if $input.fam_feld_warn_changes}family_field_with_warning{/if}" {$input.colspan}>
			{$input.chk_preferred}
			<input type="text" name="{$input.name}" value="{$input.value}" {$input.params} size="40" />
			</td>

		{elseif $input.type == "tinyint"}
			<td class="formular_content" {$input.colspan}>
			<input type="checkbox" name="{$input.name}" value="1" {$input.params} />
			</td>

		{elseif $input.type == "blob" || $input.type == "text"}
			<td class="formular_content" {$input.colspan}>
			<textarea name="{$input.name}" {$input.params} cols="40" rows="3">{$input.value}</textarea>
			</td>

		{elseif $input.type == "enum"}
			<td class="formular_content" {$input.colspan}>
			<select name="{$input.name}" {$input.params}>
			{foreach key=k item=v from=$input.values}
				<option value="{$v}" {if $v == $input.value}selected="selected"{/if}>{$input.descs[$k]}</option>
			{/foreach}
			</select>
			</td>

		{elseif $input.type == "doubleselect"}
			<td class="formular_content" {$input.colspan}>
			<table><tr><td>
			<input type="hidden" name="{$input.name}" value="{$input.avalue}" />
			<input type="hidden" name="old_{$input.name}" value="{$input.avalue}" />
			<div style="font-size:x-small;">{$label_form_ds_objects}:</div>
			<select name="sel_ds1_{$input.name}" {$input.params} size="6" onclick="double_select_add(this.options[parseInt(this.selectedIndex)].text, this.options[parseInt(this.selectedIndex)].value, 'sel_ds2_{$input.name}', '{$input.name}');">
			{foreach from=$input.values item=v key=k}
				<option value="{$v}">{$input.descs.$k}</option>
			{/foreach}
			</select>
			</td><td valign="top">
			<div style="font-size:x-small;">&nbsp;</div>
			{if $input.show_moves}
				<img src="{$ko_path}images/ds_top.gif" border="0" alt="top" title="{$label_form_ds_top}" onclick="double_select_move('{$input.name}', 'top');" /><br />
				<img src="{$ko_path}images/ds_up.gif" border="0" alt="up" title="{$label_form_ds_up}" onclick="double_select_move('{$input.name}', 'up');" /><br />
				<img src="{$ko_path}images/ds_down.gif" border="0" alt="down" title="{$label_form_ds_down}" onclick="double_select_move('{$input.name}', 'down');" /><br />
				<img src="{$ko_path}images/ds_bottom.gif" border="0" alt="bottom" title="{$label_form_ds_bottom}" onclick="double_select_move('{$input.name}', 'bottom');" /><br />
			{/if}
			<img src="{$ko_path}images/ds_del.gif" alt="x" title="{$label_doubleselect_remove}" border="0" onclick="double_select_move('{$input.name}', 'del');"/>
			</td><td>
			<div style="font-size:x-small;">{$label_form_ds_assigned}:</div>
			<select name="sel_ds2_{$input.name}" {$input.params} size="6">
			{foreach from=$input.avalues item=v key=k}
				<option value="{$v}">{$input.adescs.$k}</option>
			{/foreach}
			</select>
			</td></tr></table>
			</td>

		{elseif $input.type == "groupselect"}
			<td class="formular_content" {$input.colspan}>
			<table><tr><td>
			<input type="hidden" name="{$input.name}" value="{$input.avalue}" />
			<input type="hidden" name="old_{$input.name}" value="{$input.avalue}" />
			<div style="font-size:x-small;">{$label_group}:</div>
			<select name="sel_ds0_{$input.name}" {$input.params} size="10" onclick="if(!checkList(1)) return false;fill_grouproles_select(this.options[parseInt(this.selectedIndex)].value);">
			</select>
			</td><td>
			<div style="font-size:x-small;">{$label_grouprole}:</div>
			<select name="sel_ds1_{$input.name}" {$input.params} size="10" onclick="double_select_add(this.options[parseInt(this.selectedIndex)].text, this.options[parseInt(this.selectedIndex)].value, 'sel_ds2_{$input.name}', '{$input.name}');{$input.onclick_2_add}">
			</select>
			</td><td valign="top">
			<br />
			<img src="{$ko_path}images/ds_del.gif" alt="x" title="{$label_doubleselect_remove}" border="0" onclick="{if $allow_assign}double_select_move('{$input.name}', 'del');{$input.onclick_del_add}{/if}"/>
			</td><td>
			<div style="font-size:x-small;">{$label_group_assigned}:</div>
			<select name="sel_ds2_{$input.name}" {$input.params} size="10">
			{foreach from=$input.avalues item=v key=k}
				<option value="{$v}" title="{$input.adescs.$k}">{$input.adescs.$k}</option>
			{/foreach}
			</select>
			</td></tr></table>
			</td>

		{elseif $input.type == "file"}
			<td class="formular_content" {$input.colspan}>
			<input type="file" name="{$input.name}" {$input.params} />
			{if $input.value}<br />{$input.value}{/if}
			{if $input.value}<br /><input type="checkbox" name="{$input.name2}" value="1" />{$input.value2}{/if}
			</td>

		{elseif $input.type == "_save"}
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="{$label_save}" onclick="set_action('{$tpl_action}', this)" />
			&nbsp;&nbsp;&nbsp;
			<input type="submit" name="cancel" value="{$label_cancel}" onclick="set_action('show_all', this)" />
		</td>

		{elseif $input.type == "html"}
			<td class="formular_content" {$input.colspan}>
			{$input.value}
			</td>

		{elseif $input.type == "header"}
			<td colspan="2"><br /></td>
			</tr><tr>
			<td colspan="2" class="subpart_header">{$input.value}</td>

		{elseif $input.type == "   "}
			<td colspan="2"><br /></td>

		{else}
			<td class="formular_content" {$input.colspan}>
				{include file="$ko_path/templates/ko_formular_elements.tmpl"}
			</td>

		{/if}
	{/foreach}
	</tr>

{/section}
</table>

<table width="100%"><tr><td align="center">
	<table><tr>
		<td valign="top" width="210">
			<input type="submit" name="submit" value="{$label_save}" onclick="set_action('{$tpl_action}', this)" />
			&nbsp;&nbsp;&nbsp;
			<input type="submit" name="cancel" value="{$label_cancel}" onclick="set_action('show_all', this)" />
			{if $tpl_action_neu != ""}
				<br /><br />
				<input type="submit" name="submit_neu" value="{$label_as_new_person}" onclick="set_action('{$tpl_action_neu}', this)" />
			{/if}
		</td>
	{if $announce_values}
		<td valign="top" width="250">
				<div style="font-weight: bold;"><a href="#" onclick="change_vis('leute_announce_changes'); return false;">&raquo;&nbsp;{$label_announce_title}</a></div>
				<div id="leute_announce_changes" style="visibility: hidden; display: none;">
					<div style="font-size: small;">{$label_announce_description}</div>
					<select name="sel_announce_changes[]" multiple="multiple" size="4" style="width: 200px;">
						{foreach from=$announce_values item=v key=k}
							<option value="{$v}">{$announce_descs.$k}</option>
						{/foreach}
					</select>
				</div>
		</td>
	{/if}
	</tr></table>
</td></tr></table>

{if $tpl_legend}
	<div style="margin-top: 10px; color: #666;">
		<img src="{$ko_path}images/{$tpl_legend_icon}" alt="legend" border="0" align="left" />&nbsp;
		{$tpl_legend}
	</div>
{/if}

</td></tr>

</table>
