<table width="100%" cellspacing="0">
{if $tpl_show_header}
	<tr>
	<td class="subpart_header">
	{$tpl_title1}
	</td>
	<td>&nbsp;</td>
	</tr>
{/if}


{if $tpl_show_rec_link && ($txt_empfaenger != '' || $tpl_ohne_email != '')}
	<tr><td colspan="2" class="subpart">

	<table width="100%" border="0">

	<tr><td>
	{if $txt_empfaenger != ''}
		{$tpl_body1}<br />
		&raquo;&nbsp;<a href="mailto:{$txt_empfaenger}"><b>{$tpl_all_recip}</b></a>
		<br />
		{if $txt_empfaenger_semicolon != ''}
			&raquo;&nbsp;<a href="mailto:{$txt_empfaenger_semicolon}"><b>{$tpl_all_recip_semicolon}</b></a>
			<br />
		{/if}
	{/if}
	<br />

	{if $tpl_ohne_email != ''}
		<strong><big>&middot;</big></strong>{$tpl_no_email}&nbsp;&nbsp;
		{if $xls_filename}
		<a href="{$xls_filename}">{$tpl_xls_file}</a>
		{/if}
		<br />
		{$tpl_ohne_email}
	{/if}
	</td></tr>
	</table>

	</td></tr>


	<tr><td><br /></td></tr>
{/if}



{if $tpl_show_header}
	<tr>
	<td class="subpart_header">
	{$tpl_title2}
	</td>
	<td>&nbsp;</td>
	</tr>
{/if}

<tr><td colspan="2" class="subpart">

<table width="100%" border="0">
<tr><td colspan="2">
{$tpl_body2}<br /><br />
</td></tr>

<tr><td align="right">
{if $tpl_show_additional_rec}{$tpl_more} {/if}{$tpl_to}
</td><td align="left">
<input type="text" name="txt_empfaenger" size="80" value="{$txt_empfaenger}"{if $to_readonly} readonly="readonly"{/if} />&nbsp;
<img src="{$ko_path}images/icon_exchange_comma.gif" border="0" onclick="javascript:exchangeComma(document.getElementsByName('txt_empfaenger')[0]);" />
{if $tpl_show_to_bcc}
	&nbsp;
	<img src="{$ko_path}images/icon_to_bcc.gif" border="0" onclick="javascript:to=document.getElementsByName('txt_empfaenger')[0]; bcc=document.getElementsByName('txt_bcc')[0];bcc.value=bcc.value+(bcc.value?',':'')+to.value;to.value='{$tpl_info_email}';" />
{/if}
</td></tr>


<tr><td align="right">
{$tpl_cc}
</td><td align="left">
<input type="text" name="txt_cc" size="80" value="{$txt_cc}"{if $cc_readonly} readonly="readonly"{/if} />
</td></tr>


<tr><td align="right">
{$tpl_bcc}
</td><td align="left">
<input type="text" name="txt_bcc" size="80" value="{$txt_bcc}"{if $bcc_readonly} readonly="readonly"{/if} />
</td></tr>


<tr><td align="right">
{$tpl_subject} *
</td><td align="left">
<input type="text" name="txt_betreff" size="80" value="{$txt_betreff}" />
</td></tr>


<tr><td align="right" valign="top">
{$tpl_text}
</td><td align="left">
<textarea cols="80" rows="10" name="txt_emailtext" style="font-family: monospace;">
{$txt_emailtext}
</textarea>
</td></tr>

{if $tpl_show_bcc_an_mich}
<tr><td align="right">
{$tpl_bcc_me}
</td><td align="left">
<input type="radio" name="rd_bcc_an_mich" value="ja" />{$tpl_yes}
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="rd_bcc_an_mich" value="nein" checked="checked" />{$tpl_no}
</td></tr>
{/if}




</table>

</td></tr>
</table>


{if $tpl_show_send}
<p align="center">
<input type="hidden" name="ohne_email" value="{$tpl_ohne_email}" />
<input type="hidden" name="res_ids" value="{$tpl_res_ids}" />
<input type="submit" value="{$tpl_send}" name="submit" onclick="s=document.getElementsByName('txt_betreff')[0]; if(s.value == '') {literal}{{/literal} alert('{$tpl_error_no_subject}'); return false; {literal}} else {{/literal} set_action('submit_email', this);this.submit; {literal}}{/literal}" />
</p>
{/if}
