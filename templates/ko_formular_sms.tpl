{if $tpl_show_header}
	<h3>{$tpl_send_sms}</h3>
{/if}
<i class="text-hidden"><small>{$tpl_sms_bal} {$sms_balance}</small></i>

{if $tpl_show_recipients}
	{if $tpl_num_recipients > 0}
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">{$tpl_sms_receiver} ({$tpl_num_recipients})</h4>
			</div>
			<div class="panel-body">
				{$tpl_recipients_names}
			</div>
		</div>
	{/if}
	{if $tpl_recipients_invalid != ""}
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">{$tpl_sms_no_number}</h4>
			</div>
			<div class="panel-body">
				<div class="btn-group btn-group-sm">
					<button class="btn btn-default disabled">{ll key="leute_label_sms_form_no_address_action"}</button>
					<a class="btn btn-sm btn-primary" href="{$xls_filename}" title="{ll key="export_excel"}"><i class="fa fa-file-excel-o"></i></a>
					<button type="submit" class="btn btm-sm btn-primary" onclick="set_action('export_sms_to_mylist', this);this.submit;" title="{$tpl_sms_my_export}"><img src="{$ko_path}images/icon_export_my_list.png"></button>
					<button type="submit" class="btn btm-sm btn-primary" onclick="set_action('exportadd_sms_to_mylist', this);this.submit;" title="{$tpl_sms_my_add}"><img src="{$ko_path}images/icon_exportadd_my_list.png"></button>
				</div>
				<br><br>
				{$tpl_recipients_invalid}
			</div>
		</div>
	{/if}
{/if}

<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title">
			{ll key="leute_label_sms_form_message"}
		</h4>
	</div>
	<div class="panel-body">
		<div class="col-md-8 col-md-offset-2">
			<table class="table table-condensed full-width" style="margin: 0px auto;">
				{if $tpl_show_sender}
					<tr>
						<td>
							<b>{$tpl_sms_sender}</b>
						</td>
						<td>
							<select class="input-sm form-control" name="sel_sender">
								{foreach from=$tpl_senders item=v}
									<option value="{$v}">{$v}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				{/if}
				{if $tpl_show_recipients}
					<tr>
						<td>
							<b>{$tpl_sms_receiver}</b>
						</td>
						<td>
							<input class="input-sm form-control" type="text" name="txt_recipients" value="{$tpl_recipients}" />
						</td>
					</tr>
				{/if}
				<tr>
					<td>{$tpl_sms_add_receiver}</td>
					<td>
						<input class="input-sm form-control" type="text" name="txt_recipients_add" value="{$tpl_recipients_add}" />
					</td>
				</tr>
				<tr>
					<td valign="top">
						<b>{$tpl_sms_text}</b>
						<br /><br />
						<span id="num_letters">0/160<br />#SMS: 1</span>
					</td>
					<td>
						<textarea class="input-sm form-control" name="txt_smstext" rows="20" onkeyup="printLength(this.value);">{$txt_text}</textarea>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>


{if $crm_contact_tpl_groups}
	{assign var="tpl_groups" value=$crm_contact_tpl_groups}
	{assign var="tpl_hide_cancel" value=TRUE}
	{assign var="tpl_special_submit" value="&nbsp"}
	{include file="ko_formular.tpl"}
{/if}

<p align="center">
<input type="hidden" name="hid_sms_balance" value="{$sms_balance}" />
<input type="hidden" name="hid_recipients_invalid_ids" value="{$tpl_recipients_invalid_ids}" />
<input type="hidden" name="hid_xls_filename" value="{$xls_filename}" />
<input type="hidden" name="hid_recipient_ids" value="{$tpl_recipient_ids}">
<input type="hidden" name="hid_recipients_invalid" value="{$tpl_recipients_invalid}" />
<input type="hidden" name="hid_recipients_names" value="{$tpl_recipients_names}" />
{if $tpl_show_sendbutton}
	<button class="btn btn-sm btn-primary" type="submit" value="{$tpl_sms_submit}" name="submit" onclick="set_action('submit_sms', this);this.submit">{$tpl_sms_submit}</button>
{/if}
</p>
