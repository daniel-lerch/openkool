<div id="project_header" name="project_header" style="border:1px solid #666;">
<table width="100%">
<tr>
<td width="50%" align="left">
<b>{$project.number}</b> {$project.title}<br />
</td>
<td width="50%" align="right">
<div id="project_status" name="project_status" style="padding-right:20px;">
{$status_div}
</div>
</td>
</tr>

<tr>
<td width="50%" align="left" valign="top">
<i>{$project.description}</i>
</td>
<td width="50%" align="right">
<table align="right"><tr><td>
{foreach from=$project.clients key=type item=client}
	{foreach from=$client item=person}
		<b>{$person.client_type}:</b> <a href="/leute/index.php?action=single_view&amp;id={$person.id}">{$person.vorname} {$person.nachname}{if $person.firm} ({$person.firm}){/if}</a><br />
	{/foreach}
{/foreach}
</td></tr></table>
</td>
</tr>
</table>

</div>



<div id="project_actions" name="project_actions" style="margin-top:20px;">
	<b>{$title_actions}</b><br />
	<a href="#" onclick="change_vis('add_bill');">Rechnung</a>&nbsp;&nbsp;&nbsp;
	<a href="?action=add_reminder&amp;id={$project_id}">Mahnung</a>&nbsp;&nbsp;&nbsp;
	<a href="#" onclick="change_vis('add_payment');">Zahlungseingang</a>&nbsp;&nbsp;&nbsp;
	<a href="?action=project_xls_export&amp;id={$project_id}">Excel-Export</a>
	<div id="add_bill" name="add_bill" style="display:none; visibility: hidden;">
		<table>
			<tr><td valign="bottom">
			Rechnungsbetrag:<br />
			<input type="text" name="txt_price" />
			</td><td valign="bottom">
			Referenz-Nummer:<br />
			<input type="text" name="txt_refnr" />
			</td><td valign="bottom">
			<input type="submit" name="submit_add_bill" onclick="set_hidden_value('id', '{$project_id}', this); set_action('issue_bill', this);" value="OK" />
			</td></tr>
		</table>
	</div>
	<div id="add_payment" name="add_payment" style="display:none; visibility: hidden;">
		<table>
			<tr><td valign="bottom">
			Betrag:<br />
			<input type="text" name="txt_amount" value="{$billed_amount}" />
			</td><td valign="bottom">
			<input type="submit" name="submit_add_payment" onclick="set_hidden_value('id', '{$project_id}', this); set_action('submit_add_payment', this);" value="OK" />
			</td></tr>
		</table>
	</div>
</div>



{include file='ko_project_todos.tpl'}



<div id="project_logs" name="project_logs" style="margin-top:20px;">
<b>{$title_logs}</b>
<div onclick="change_vis_tr('new_log_entry');">{$label_new_log_entry}</div>
<table width="100%" border="0">
<tr>
{foreach from=$log_headers item=header key=key}
	<th class="ko_list">
	{if $header}
		{assign var="type" value="disabled"}
		{if $sort == $key AND $sort_order == "DESC"} {assign var="type" value="enabled"} {/if}
		<a href="javascript:sendReq('../projects/inc/ajax.php', 'action,sort,sort_order,sesid', 'logsort,{$key},DESC,{$sesid}', do_element);">
		<img src="{$ko_path}images/icon_arrow_down_{$type}.gif" alt="sort" border="0" title="{$label_list_sort_desc}" />
		</a>
	{/if}

	{$header}

	{if $header}
		{assign var="type" value="disabled"}
		{if $sort == $key AND $sort_order == "ASC"} {assign var="type" value="enabled"} {/if}
		<a href="javascript:sendReq('../projects/inc/ajax.php', 'action,sort,sort_order,sesid', 'logsort,{$key},ASC,{$sesid}', do_element);">
		<img src="{$ko_path}images/icon_arrow_up_{$type}.gif" alt="sort" border="0" title="{$label_list_sort_asc}" />
		</a>
	{/if}
	</th>
{/foreach}
</tr>

<tr style="display:none;" id="new_log_entry" name="new_log_entry">
<td colspan="2">&nbsp;</td>
<td valign="top" align="center"><input type="text" name="new_entry[time]" value="{$new_entry.time}" /></td>
<td valign="top" align="center"><select name="new_entry[type]" size="0">{html_options values=$new_entry.values output=$new_entry.output}</select></td>
<td valign="top" align="center"><textarea name="new_entry[comment]" cols="50" rows="3"></textarea></td>
<td valign="top" align="center">{$new_entry.user}</td>
<td valign="top" align="center"><input type="text" name="new_entry[hours]" size="3" />h&nbsp;&nbsp;&nbsp;<input type="text" name="new_entry[rate]" size="3" />CHF&nbsp;<br /><input type="text" name="new_entry[costs]" size="3" />CHF</td>
<td valign="top" align="center"><input type="checkbox" name="new_entry[chargeable]" value="1" /><br /><input type="submit" name="submit_new_log_entry" value="{$new_entry.label_save}" onclick="set_action('submit_new_log_entry', this);"></td>
</tr>

{foreach from=$project.logs item=log}
	<tr class="{cycle values="ko_list_even, ko_list_odd"}">
	<td><input type="image" src="{$ko_path}images/button_edit.gif" onclick="change_vis_tr('edit_log_entry_{$log.id}');return false;" /></td>
	<td><input type="image" src="{$ko_path}images/button_delete.gif" onclick="set_action('delete_log', this);set_hidden_value('id', '{$log.id}', this)" /></td>
	<td><span style="white-space:nowrap;">{$log.date}</span> {$log.time}</td>
	<td>{$log.type}</td>
	<td>{$log.comment}</td>
	<td>{$log.username}</td>
	<td>{$log.costs_all}</td>
	<td>{$log.chargeable}</td>
	</tr>

	<tr style="display:none;" id="edit_log_entry_{$log.id}" name="edit_log_entry_{$log.id}">
	<td colspan="2">&nbsp;</td>
	<td valign="top" align="center"><input type="text" name="edit_entry[{$log.id}][time]" value="{$log.time_value}" /></td>
	<td valign="top" align="center"><select name="edit_entry[{$log.id}][type]" size="0">{html_options values=$new_entry.values output=$new_entry.output selected=$log.type_value}</select></td>
	<td valign="top" align="center"><textarea name="edit_entry[{$log.id}][comment]" cols="50" rows="3">{$log.comment}</textarea></td>
	<td valign="top" align="center">{$log.username}</td>
	<td valign="top" align="center"><input type="text" name="edit_entry[{$log.id}][hours]" size="3" value="{$log.hours}" />h&nbsp;&nbsp;&nbsp;<input type="text" name="edit_entry[{$log.id}][rate]" size="3" value="{$log.rate}" />CHF&nbsp;<br /><input type="text" name="edit_entry[{$log.id}][costs]" size="3" value="{$log.costs}" />CHF</td>
	<td valign="top" align="center"><input type="checkbox" name="edit_entry[{$log.id}][chargeable]" value="1" {if $log.chargeable}checked="checked"{/if} /><br /><input type="submit" name="submit_edit_log_entry" value="{$new_entry.label_save}" onclick="set_action('submit_edit_log_entry', this); set_hidden_value('id', '{$log.id}', this);"></td>
	</tr>

{/foreach}
</table>
</div>

<div style="padding-top:10px;" align="right">
{$footer}
</div>
