<table width="100%" cellspacing="0">
<tr>
<td class="subpart_header">
{$label_title}
</td>
<td>&nbsp;</td>
</tr>

<tr><td colspan="2" class="subpart">

<table border="0" align="center">
<tr> <td>{$label_preset}:</td>
<td>
<select name="sel_vorlage" size="0">
{foreach from=$vorlagen.values item=v key=k}
	<option value="{$v}" {if $v == $vorlagen.value}selected="selected"{/if}>{$vorlagen.output.$k}</option>
{/foreach}
</select>
</td> </tr>
<tr><td>{$label_start}:</td>
<td><input type="text" name="txt_start" size="10" value="1" />
</td></tr>
<tr><td>{$label_border}:</td>
<td><input type="radio" name="rd_rahmen" value="ja" />{$label_yes}
&nbsp;&nbsp;
<input type="radio" name="rd_rahmen" value="nein" checked="checked" />{$label_no}
</td></tr>
<tr><td>{$label_fill_page}:</td>
<td>
<input type="checkbox" name="chk_fill_page" onclick="if(this.checked) document.formular.txt_fill_page.style.visibility = 'visible'; else document.formular.txt_fill_page.style.visibility = 'hidden';" />
<input type="text" name="txt_fill_page" value="1" size="4" style="visibility:hidden;" />
</td></tr>
<tr><td>{$label_multiplyer}:</td>
<td><input type="text" name="txt_multiply" size="10" value="1" />
</td></tr>
<tr><td>
		{$label_return_address}:
</td><td>
	<input type="checkbox" name="chk_return_address" {if $return_address_chk}checked="checked"{/if} />
</td></tr>
<tr id="extended_return_address" style="{if !$return_address_chk}display:none;{/if}">
	<td colspan="2" style="padding:5px 10px;">
		<select name="sel_return_address" style="max-width: 100%">
			{if $return_address_info}<option value="info_address" {if $return_address_sel == "info_address"}selected="selected"{/if}>{$return_address_info}</option>
			<option value="info_address_pp" {if $return_address_sel == "info_address_pp"}selected="selected"{/if}>{ll key="leute_return_address_pp"} {$return_address_info}</option>{/if}
			{if $return_address_login}<option value="login_address" {if $return_address_sel == "login_address"}selected="selected"{/if}>{$return_address_login}</option>
			<option value="login_address_pp" {if $return_address_sel == "login_address_pp"}selected="selected"{/if}>{ll key="leute_return_address_pp"} {$return_address_login}</option>{/if}
			<option value="manual_address" {if $return_address_sel == "manual_address"}selected="selected"{/if}>{ll key="leute_labels_manual_address"}</option>
		</select>
		<br />
		<input id="manual_return_address" type="text" size="50" name="txt_return_address" placeholder="{ll key="leute_labels_manual_address_placeholder"}" value="{$return_address_txt}" style="width:100%;margin-top:4px;{if $return_address_sel != "manual_address"} display:none;{/if}" />
	</td>
</tr>

<tr><td colspan="2">
<fieldset>
<legend>{$label_limiter}</legend>


<div>
	{assign var='last_col' value=''}
	{foreach from=$tpl_cols item=col}
		{if $col.type == 'empty'}
			<div class="empty_address_line"><input type="text" name="{$col.name}"><img title="{ll key="leute_labels_etiquette_export_show_empty_line"}" class="show_button" src="{$ko_path}images/icon_plus.png"><img title="{ll key="leute_labels_etiquette_export_hide_empty_line"}" class="hide_button" src="{$ko_path}images/icon_minus.png">
		{else}
			<div style="height:24px;"><label class="address_line_label">{$col.name}</label>
		{/if}
		{if $col.show_select}
			<select name="sel_col_{$col.id}" size="0">
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
		{if $col.type != 'empty'}
			{assign var='last_col' value=$col.id}
		{/if}
	{/foreach}
</div>

</fieldset>

</td></tr>


</table>

</td></tr>
</table>


<p align="center">
<input type="submit" value="{$label_submit}" name="submit" onclick="set_action('submit_etiketten', this);this.submit" />
</p>
