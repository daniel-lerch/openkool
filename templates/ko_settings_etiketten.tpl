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
<table width="100%" cellspacing="0"><tr><td class="block_header">

<table align="center">
<tr><td>{$label_preset}:</td>
<td><select size="0" name="sel_vorlage_open" onchange="jumpToUrl('?action=open_etiketten&amp;sel_vorlage_open='+this.options[this.selectedIndex].value)">
{foreach from=$vorlagen.values item=v key=k}
	<option value="{$v}" {if $v == $vorlagen.value}selected="selected"{/if}>{$vorlagen.output.$k}</option>
{/foreach}
</select>
&nbsp;&nbsp;&nbsp;
<input type="submit" name="submit_open_vorlage" value="{$label_open}" onclick="set_action('open_etiketten', this);this.submit;" />
&nbsp;&nbsp;&nbsp;
<input type="submit" name="submit_delete_vorlage" value="{$label_delete}" onclick="c=confirm('{$label_delete_confirm}');if(!c) return false;set_action('submit_del_etiketten_vorlage', this);this.submit;" />
</td></tr>
</table>

</td></tr>



<tr><td class="block_content">

<table align="center">
<tr><td>{$label_page_format}</td>
<td><select name="sel_pageformat" size="0">
{foreach from=$page_format.values item=v key=k}
	<option value="{$v}" {if $v == $page_format.value}selected="selected"{/if}>{$page_format.output.$k}</option>
{/foreach}
</select></td></tr>

<tr><td>{$label_page_orientation}</td>
<td><select name="sel_pageorientation" size="0">
{foreach from=$page_orientation.values item=v key=k}
	<option value="{$v}" {if $v == $page_orientation.value}selected="selected"{/if}>{$page_orientation.output.$k}</option>
{/foreach}
</select></td></tr>


<tr><td>{$label_per_row}</td>
<td><input type="text" name="txt_per_row" size="5" value="{$txt_per_row}" /></td></tr>

<tr><td>{$label_per_col}</td>
<td><input type="text" name="txt_per_col" size="5" value="{$txt_per_col}" /></td></tr>

<tr><td>{$label_border_top}</td>
<td><input type="text" name="txt_border_top" size="5" value="{$txt_border_top}" /></td></tr>

<tr><td>{$label_border_right}</td>
<td><input type="text" name="txt_border_right" size="5" value="{$txt_border_right}" /></td></tr>

<tr><td>{$label_border_bottom}</td>
<td><input type="text" name="txt_border_bottom" size="5" value="{$txt_border_bottom}" /></td></tr>

<tr><td>{$label_border_left}</td>
<td><input type="text" name="txt_border_left" size="5" value="{$txt_border_left}" /></td></tr>

<tr><td>{$label_spacing_horiz}</td>
<td><input type="text" name="txt_spacing_horiz" size="5" value="{$txt_spacing_horiz}" /></td></tr>

<tr><td>{$label_spacing_vert}</td>
<td><input type="text" name="txt_spacing_vert" size="5" value="{$txt_spacing_vert}" /></td></tr>

<tr><td>{$label_align_horiz}</td>
<td><select name="sel_align_horiz" size="0">
{foreach from=$textalignh.values item=v key=k}
	<option value="{$v}" {if $v == $textalignh.value}selected="selected"{/if}>{$textalignh.output.$k}</option>
{/foreach}
</select></td></tr>

<tr><td>{$label_align_vert}</td>
<td><select name="sel_align_vert" size="0">
{foreach from=$textalignv.values item=v key=k}
	<option value="{$v}" {if $v == $textalignv.value}selected="selected"{/if}>{$textalignv.output.$k}</option>
{/foreach}
</select></td></tr>

<tr><td>{$label_font}</td>
<td><select name="sel_font" size="0">
{foreach from=$font.values item=v key=k}
	<option value="{$v}" {if $v == $font.value}selected="selected"{/if}>{$font.output.$k}</option>
{/foreach}
</select></td></tr>

<tr><td>{$label_textsize}</td>
<td><select name="sel_textsize" size="0">
{foreach from=$textsize.values item=v key=k}
	<option value="{$v}" {if $v == $textsize.value}selected="selected"{/if}>{$textsize.output.$k}</option>
{/foreach}
</select></td></tr>

<tr><td><br />{$label_ra_font}</td>
<td><br /><select name="sel_ra_font" size="0">
{foreach from=$ra_font.values item=v key=k}
	<option value="{$v}" {if $v == $ra_font.value}selected="selected"{/if}>{$ra_font.output.$k}</option>
{/foreach}
</select></td></tr>

<tr><td>{$label_ra_size}</td>
<td><select name="sel_ra_textsize" size="0">
{foreach from=$ra_textsize.values item=v key=k}
	<option value="{$v}" {if $v == $ra_textsize.value}selected="selected"{/if}>{$ra_textsize.output.$k}</option>
{/foreach}
</select></td></tr>

<tr><td>{$label_ra_margin_top}</td>
<td><input type="text" name="txt_ra_margin_top" size="5" value="{$txt_ra_margin_top}" /></td></tr>

<tr><td>{$label_ra_margin_left}</td>
<td><input type="text" name="txt_ra_margin_left" size="5" value="{$txt_ra_margin_left}" /></td></tr>

<tr><td><br />{$label_pic_file}</td>
<td><br />
	{if $pic_file}
		<span name="label_pic">
			{$pic_file}
			<a href="" onclick="sendReq('../admin/inc/ajax.php', 'action,id,span,sesid', 'deletepic,{$vorlagen.value},label_pic,{$sesid}', do_element);">
				<img src="{$ko_path}images/button_delete.gif" border="0" />
			</a>
		</span>
	{/if}
	<input type="file" name="pic_file" size="20" />
</td></tr>

<tr><td>{$label_pic_w}</td>
<td><input type="text" name="txt_pic_w" size="5" value="{$txt_pic_w}" /></td></tr>

<tr><td>{$label_pic_x}</td>
<td><input type="text" name="txt_pic_x" size="5" value="{$txt_pic_x}" /></td></tr>

<tr><td>{$label_pic_y}</td>
<td><input type="text" name="txt_pic_y" size="5" value="{$txt_pic_y}" /></td></tr>

</table>
</td></tr></table>

<p align="center">
<input type="reset" name="cancel" value="{$label_reset}" />
&nbsp;&nbsp;&nbsp;
<input type="submit" name="submit" value="{$label_save}" onclick="set_action('{$tpl_action}', this)" />
&nbsp;&nbsp;&nbsp; als &nbsp;&nbsp;&nbsp;
<select size="0" name="sel_vorlage_save">
{foreach from=$vorlagen.values item=v key=k}
	<option value="{$v}" {if $v == $vorlagen.value}selected="selected"{/if}>{$vorlagen.output.$k}</option>
{/foreach}
</select>
&nbsp;&nbsp;&nbsp; {$label_or_new} &nbsp;&nbsp;&nbsp;
<input type="text" name="txt_vorlage_neu" size="20" />
</p>

</td></tr>

</table>
