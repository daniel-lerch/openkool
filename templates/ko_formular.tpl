{foreach item=hid from=$tpl_hidden_inputs}
	<input type="hidden" name="{$hid.name}" value="{$hid.value}" />
{/foreach}
{if $tpl_id}
	<input type="hidden" name="id" value="{$tpl_id}" />
{/if}

<table width="100%" cellspacing="0"><tr class="header_row"><td class="subpart_header">
{$tpl_titel}
</td>
<td align="left" style="padding-left: 5px;">
	{if $help.show}{$help.link}{/if}
</td>
<td align="right">
&nbsp;
</td></tr>

<tr><td class="subpart" colspan="2">

<table border="0" width="100%" cellspacing="0" cellpadding="0">

<!-- Formular-Daten -->
{foreach key=id name=groups item=group from=$tpl_groups}
	{if $group.titel != ""}
		{if $group.forAll}
			<tr><td colspan="2" class="forAllHeader">
			<span class="form_grouptitel" onclick="forAllHeader('frmgrp_{$id}', 'koi[{$group.table}][doForAll]');">
				<input type="checkbox" name="koi[{$group.table}][doForAll]" id="koi[{$group.table}][doForAll]" />
				&rarr;&nbsp;{$group.titel}
			</span>
		{else}
			<tr><td {$group.colspan}>
			<span class="form_grouptitel" onclick="change_vis('frmgrp_{$id}');">&rarr;&nbsp;{$group.titel}</span>
		{/if}

		{if $group.state == "closed"}
			<div id="frmgrp_{$id}" class="form_divider" style="visibility:hidden;display:none;">
		{else}
			<div id="frmgrp_{$id}" class="form_divider" style="visibility:visible;display:block;">
		{/if}
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
	{/if}
	{foreach name=rows item=row from=$group.row}

		<tr>
		{foreach name=inputs item=input from=$row.inputs}
			<td class="{if $input.headerclass}{$input.headerclass}{else}formular_header{/if}" {$input.colspan}>
			{if $input.descimg}<img src="{$ko_path}images/{$input.descimg}" border="0" />{/if}
			{$input.desc}
			{if $input.help}{$input.help}{/if}
			</td>
		{/foreach}
		</tr>

		<tr>
		{foreach name=inputs item=input from=$row.inputs}
			<td class="formular_content" {$input.colspan}>
				{include file="$ko_path/templates/ko_formular_elements.tmpl"}
			</td>
		{/foreach}
		</tr>

	{/foreach}
	{if $group.titel != ""}
		</table>
		</div>
		</td></tr>
	{/if}

{/foreach}
</table>

<p align="center">
{if $tpl_special_submit}
	{$tpl_special_submit}
{else}
	<input type="submit" name="submit" class="ko_form_submit {$submit_class}" value="{$tpl_submit_value}" onclick="{$tpl_onclick_action}set_action('{$tpl_action}', this)" />
{/if}
{if !$tpl_hide_cancel}
	&nbsp;&nbsp;&nbsp;
	<input type="submit" name="cancel" value="{$label_cancel}" onclick="set_action('{$tpl_cancel}', this);" />
{/if}
{if $tpl_submit_as_new && !$force_hide_submit_as_new}
	<br /><br />
	<input type="submit" name="submit_as_new" value="{$tpl_submit_as_new}" onclick="set_action('{$tpl_action_as_new}', this);" />
{/if}
</p>

{if $tpl_legend}
	<div style="margin-top: 10px; color: #666;">
		<img src="{$ko_path}images/{$tpl_legend_icon}" alt="legend" border="0" align="left" />&nbsp;
		{$tpl_legend}
	</div>
{/if}

</td></tr>

</table>
