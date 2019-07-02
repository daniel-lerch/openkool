<table width="100%" cellspacing="0"><tr><td class="subpart_header">
{$tpl_titel}
</td>
<td align="left" style="padding-left: 5px;">
	{if $help.show}{$help.link}{/if}
</td>
<td align="right">
&nbsp;
</td></tr>
                                                                                                                    
<tr><td class="subpart" colspan="3">


<table width="100%" cellspacing="0" border="0">
{section name=part loop=$tpl_parts}
	<tr><td class="block_header"><b>{$tpl_parts[part].titel}</b></td></tr>
	<tr><td class="block_content">
	<table border="0">
	{foreach name=settings item=setting from=$tpl_parts[part].settings}
	<tr>
	<td align="right" valign="top"><b>{$setting.desc}</b></td>
	<td>

	{if $setting.type == "text"}
		<input type="text" name="{$setting.name}" value="{$setting.value}" {$setting.params} />

	{elseif $setting.type == "password"}
		<input type="password" name="{$setting.name}" value="{$setting.value}" {$setting.params} />

	{elseif $setting.type == "textarea"}
		<textarea name="{$setting.name}" {$setting.params}>{$setting.value}</textarea>

	{elseif $setting.type == "checkbox"}
		<input type="checkbox" name="{$setting.name}" value="1" {$setting.params} />
	
	{elseif $setting.type == 'switch'}
		<input type="hidden" name="{$setting.name}" id="{$setting.name}" value="{$setting.value}" />{$setting.desc2}
		<div class="input_switch switch_state_{$setting.value}" name="switch_{$setting.name}">
			<label class="switch_state_label_0" {if $setting.value == 1}style="display: none;"{/if}>{$setting.label_0}</label>
			<label class="switch_state_label_1" {if $setting.value == 0}style="display: none;"{/if}>{$setting.label_1}</label>
		</div>

	{elseif $setting.type == "select"}
		<select name="{$setting.name}" {$setting.params}>
		{foreach from=$setting.values item=v key=k}
			<option value="{$v}" {if $v == $setting.value}selected="selected"{/if}>{$setting.descs.$k}</option>
		{/foreach}
		</select>

	{elseif $setting.type == "radio"}
		{html_radios name=$setting.name values=$setting.value checked=$setting.checked output=$setting.output separator="&nbsp;&nbsp; &nbsp;"}

	{elseif $setting.type == "doubleselect"}
		<table><tr><td>
		<input type="hidden" name="{$setting.name}" value="{$setting.avalue}" />
		<input type="hidden" name="old_{$setting.name}" value="{$setting.avalue}" />
		<select name="sel_ds1_{$setting.name}" {$setting.params} onclick="double_select_add(this.options[parseInt(this.selectedIndex)].text, this.options[parseInt(this.selectedIndex)].value, 'sel_ds2_{$setting.name}', '{$setting.name}');">
		{foreach from=$setting.values item=v key=k}
			<option value="{$v}">{$setting.descs.$k}</option>
		{/foreach}
		</select>
		</td><td valign="top">
		{if $setting.show_moves}
			<img src="{$ko_path}images/ds_top.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'top');" /><br />
			<img src="{$ko_path}images/ds_up.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'up');" /><br />
			<img src="{$ko_path}images/ds_down.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'down');" /><br />
			<img src="{$ko_path}images/ds_bottom.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'bottom');" /><br />
			<img src="{$ko_path}images/ds_del.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'del');" />
		{else}
		<img src="{$ko_path}images/button_delete.gif" alt="{$label_doubleselect_remove}" title="{$label_doubleselect_remove}" border="0" onclick="double_select_move('{$setting.name}', 'del');"/>
		{/if}
		</td><td>
		<select name="sel_ds2_{$setting.name}" {$setting.params}>
		{foreach from=$setting.avalues item=v key=k}
			<option value="{$v}">{$setting.adescs.$k}</option>
		{/foreach}
		</select>
		</td></tr></table>

	{elseif $setting.type == "html"}
		{$setting.value}

	{/if}
	</td></tr>
	{/foreach}

	</table>
	</td></tr>
	<tr><td><br /></td></tr>
{/section}
</table>

<p align="center">
<input type="submit" name="submit" value="{$label_save}" onclick="set_action('{$tpl_action}', this)" />
&nbsp;&nbsp;&nbsp;
<input type="reset" name="cancel" value="{$label_reset}" />
</p>

</td></tr>

</table>
