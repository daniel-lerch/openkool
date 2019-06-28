<table width="100%" cellspacing="0">
{if $tpl_show_header}
	<tr>
	<td class="subpart_header">
	{$tpl_send_sms}
	</td>
	<td>&nbsp;</td>
	</tr>
{/if}

<tr><td colspan="2" class="subpart">
<font color="gray"><small>{$tpl_sms_bal} {$sms_balance}</small></font>
<br />
{if $tpl_show_recipients}
	- {$tpl_sms_receiver} ({$tpl_num_recipients})<br />
	{$tpl_recipients_names}
	<br /><br />
	{if $tpl_recipients_invalid != ""}
		{$tpl_sms_no_number} &nbsp;&nbsp;
		<a href="{$xls_filename}">{$tpl_sms_excel_file}</a> &nbsp;&nbsp;
		<input type="image" src="{$ko_path}images/icon_export_my_list.png" onclick="set_action('export_sms_to_mylist', this);this.submit;" title="{$tpl_sms_my_export}" /> &nbsp;&nbsp;
		<input type="image" src="{$ko_path}images/icon_exportadd_my_list.png" onclick="set_action('exportadd_sms_to_mylist', this);this.submit;" title="{$tpl_sms_my_add}" />
		<br />
		{$tpl_recipients_invalid}
		<br /><br />
	{/if}
{/if}


<table border="0" align="center">
{if $tpl_show_sender}
<tr><td><b>{$tpl_sms_sender}</b></td>
<td>
	<select size="0" name="sel_sender">
		{foreach from=$tpl_senders item=v}
			<option value="{$v}">{$v}</option>
		{/foreach}
	</select>
</td></tr>
{/if}
{if $tpl_show_recipients}
<tr><td><b>{$tpl_sms_receiver}</b></td>
<td><input type="text" name="txt_recipients" size="40" value="{$tpl_recipients}" />
</td></tr>
{/if}
<tr><td>{$tpl_sms_add_receiver}</td>
<td><input type="text" name="txt_recipients_add" size="40" value="{$tpl_recipients_add}" />
</td></tr>
<tr><td valign="top">
	<b>{$tpl_sms_text}</b>
	<br /><br />
	<span id="num_letters">0/160<br />#SMS: 1</span>
</td>
<td><textarea name="txt_smstext" cols="40" rows="10" onkeyup="printLength(this.value);">{$txt_text}</textarea>
</td></tr>
</table>


</td></tr>
</table>


<p align="center">
<input type="hidden" name="hid_sms_balance" value="{$sms_balance}" />
<input type="hidden" name="hid_recipients_invalid_ids" value="{$tpl_recipients_invalid_ids}" />
<input type="hidden" name="hid_xls_filename" value="{$xls_filename}" />
<input type="hidden" name="hid_recipients_invalid" value="{$tpl_recipients_invalid}" />
<input type="hidden" name="hid_recipients_names" value="{$tpl_recipients_names}" />
{if $tpl_show_sendbutton}
	<input type="submit" value="{$tpl_sms_submit}" name="submit" onclick="set_action('submit_sms', this);this.submit" />
{/if}
</p>
