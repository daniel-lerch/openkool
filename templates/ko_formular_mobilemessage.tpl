{if $tpl_show_header}
	<h3>{ll key="leute_mobilemessage_send"}</h3>
{/if}

{if $tpl_show_sendmethods}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">{ll key="leute_mobilemethode"}</h4>
		</div>
		<div class="panel-body">
			<div class="btn-group" role="group">
				<button data-url="{$tpl_viewmodelink}id=sms" type="button" class="sendmode_switch btn {if $tpl_viewmode == "sms"}btn-primary{else}btn-default{/if}">
					{ll key="leute_mobilemethode_sms"}
				</button>
				<button data-url="{$tpl_viewmodelink}id=smstelegram" type="button" class="sendmode_switch btn {if $tpl_viewmode == "smstelegram"}btn-primary{else}btn-default{/if}">
					{ll key="leute_mobilemethode_smstelegram"}
				</button>
				<button data-url="{$tpl_viewmodelink}id=telegram" type="button" class="sendmode_switch btn {if $tpl_viewmode == "telegram"}btn-primary{else}btn-default{/if}">
					{ll key="leute_mobilemethode_telegram"}
				</button>
			</div>
		</div>
	</div>
	<script>
		$('button.sendmode_switch').click(function() {ldelim}
			var ids = [];
			var selected_sms = $('#sel_sms_people').val();
			if(selected_sms !== undefined) {ldelim}
				ids = ids.concat(selected_sms.split(","));
			{rdelim}
			var invalid_sms = $('input[name="hid_recipients_invalid_ids"]').val();
			if(invalid_sms !== undefined) {ldelim}
				ids = ids.concat(invalid_sms.split(","));
			{rdelim}
			var selected_telegram = $('#sel_telegram_people').val();
			if(selected_telegram !== undefined) {ldelim}
				ids = ids.concat(selected_telegram.split(","));
			{rdelim}

			var url = $(this).data('url');
			var newurl = url.replace(/(ids=).*?(&)/,'$1' + ids.join(",") + '$2');
			newurl = newurl.replace(/(allep)/,'markierte');
			$(location).attr('href',newurl);
		{rdelim});
	</script>
{/if}

{if ($tpl_viewmode eq "sms") || ($tpl_viewmode eq "smstelegram")}
	{if $tpl_recipients_invalid != ""}
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">{ll key="leute_sms_no_number"}</h4>
			</div>
			<div class="panel-body">
				<div class="btn-group btn-group-sm">
					<button class="btn btn-default disabled">{ll key="leute_label_sms_form_no_address_action"}</button>
					<a class="btn btn-sm btn-primary" href="{$xls_filename}" title="{ll key="export_excel"}"><i class="fa fa-file-excel-o"></i></a>
					<button class="btn btm-sm btn-primary"
							onclick="sendReq('../leute/inc/ajax.php', ['action', 'hidrecipientsinvalidids', 'sesid'], ['exportsmstomylist', '{$tpl_recipients_invalid_ids}', kOOL.sid], do_element); return false;"
							title="{ll key="leute_sms_my_export"}"><img src="{$ko_path}images/icon_export_my_list.png"></button>
					<button class="btn btm-sm btn-primary"
							onclick="sendReq('../leute/inc/ajax.php', ['action', 'hidrecipientsinvalidids', 'sesid'], ['exportaddsmstomylist', '{$tpl_recipients_invalid_ids}', kOOL.sid], do_element); return false;"
							title="{ll key="leute_sms_my_add"}"><img src="{$ko_path}images/icon_exportadd_my_list.png"></button>
				</div>
				<br><br>
				{$tpl_recipients_invalid}
			</div>
		</div>
	{/if}
{else}
	{if $tpl_recipients_invalid != ""}
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">{ll key="leute_mobile_recipients_not_send"}</h4>
			</div>
			<div class="panel-body">
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
		{if ($tpl_viewmode eq "sms") || ($tpl_viewmode eq "smstelegram")}
		<div class="col-md-6">
			<h3>{ll key="module_sms"}</h3>
			<table class="table table-condensed full-width" style="margin: 0px auto;">
				<tr>
					<td>
						<label for="sms_sender">{ll key="leute_sms_sender"}</label>
						<select class="input-sm form-control" id="sms_sender" name="sel_sender">
							{foreach from=$tpl_sms_senders item=v}
								<option value="{$v}">{$v}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<label for="sel_sms_people">{ll key="leute_sms_receiver"}</label>
						<div class="peoplesearch-wrapper">
							<input type="hidden" class="" name="sel_sms_people" id="sel_sms_people">
						</div>
						<script>
							$('#sel_sms_people').peoplesearch({ldelim}
								multiple: true,
								excludeSql: '`natel` != 0',
								avalues: {$tpl_sms_recipients_avalues},
								avalue: {$tpl_sms_recipients_avalue},
								adescs: {$tpl_sms_recipients_adescs}
								{rdelim});
						</script>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<label for="txt_sms_recipients_add">{ll key="leute_sms_add_receiver"}</label>
						<input class="input-sm form-control" type="text" name="txt_sms_recipients_add" value="{$tpl_sms_recipients_add}" />
					</td>
				</tr>
					<tr>
					<td valign="top">
						<label for="sms_text">{ll key="leute_sms_text"}</label>
						<br /><br />
						<span id="num_letters">0/160<br />#SMS: 1</span>
					</td>
					<td>
						<textarea class="input-sm form-control" id="sms_text" name="txt_smstext" rows="20" onkeyup="printLength(this.value);">{$txt_smstext}</textarea>
					</td>
				</tr>
			</table>
		</div>
		{/if}
		{if ($tpl_viewmode eq "telegram") || ($tpl_viewmode eq "smstelegram")}
		<div class="col-md-6">
			<h3>{ll key="module_telegram"}</h3>
			<table class="table table-condensed full-width" style="margin: 0px auto;">
				<tr>
					<td colspan="2">
						<label>{ll key="leute_telegram_sender"}</label>
						<div>{$tpl_leute_telegram_sender_text}</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<label for="sel_telegram_people">{ll key="leute_telegram_receiver"}</label>
						<div class="peoplesearch-wrapper">
							<input type="hidden" class="" name="sel_telegram_people" id="sel_telegram_people">
						</div>
						<script>
							$('#sel_telegram_people').peoplesearch({ldelim}
							multiple: true,
							excludeSql: '`telegram_id` != -1',
							avalues: {$tpl_telegram_recipients_avalues},
							avalue: {$tpl_telegram_recipients_avalue},
							adescs: {$tpl_telegram_recipients_adescs}
							{rdelim});
						</script>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<label for="telegram_text">{ll key="leute_sms_text"}</label>
					</td>
					<td>
						<textarea class="input-sm form-control" id="telegram_text" name="txt_telegram_text" rows="20">{$txt_telegram_text}</textarea>
						<script>
							CKEDITOR.replace( 'telegram_text', {ldelim}
							toolbar:
							[
							{ldelim} name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] {rdelim},
							{ldelim} name: 'basicstyles', items : [ 'Bold','Italic','-','RemoveFormat' ] {rdelim},
							{ldelim} name: 'links', items : [ 'Link' ] {rdelim}
							],
							removeDialogTabs: 'link:advanced;image:advanced',
							{rdelim});
						</script>
					</td>
				</tr>
			</table>
		</div>
		{/if}
</div>


{if $crm_contact_tpl_groups}
	{assign var="tpl_groups" value=$crm_contact_tpl_groups}
	{assign var="tpl_hide_cancel" value=TRUE}
	{assign var="tpl_special_submit" value="&nbsp"}
	{include file="ko_formular.tpl"}
{/if}

<p align="center">
<input type="hidden" name="hid_recipients_invalid_ids" value="{$tpl_recipients_invalid_ids}" />
<input type="hidden" name="hid_xls_filename" value="{$xls_filename}" />
<input type="hidden" name="hid_recipient_ids" value="{$tpl_recipient_ids}">
<input type="hidden" name="hid_recipients_invalid" value="{$tpl_recipients_invalid}" />
<input type="hidden" name="hid_recipients_names" value="{$tpl_recipients_names}" />
<input type="hidden" name="hid_viewmodelink" value="{$tpl_viewmodelink}" />

{if $tpl_show_sendbutton}
	<button class="btn btn-sm btn-primary" type="submit" value="{ll key="leute_sms_submit"}" name="submit" onclick="set_action('submit_{$tpl_viewmode}', this);this.submit">{ll key="leute_sms_submit"}</button>
{/if}
</p>
