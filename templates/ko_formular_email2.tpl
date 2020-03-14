<h3>{ll key="form_leute_mailing_title"}</h3>

{if $txt_empfaenger != '' || $tpl_ohne_email != ''}
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title">
				{ll key="leute_email_title1"}
			</h4>
		</div>
		<div class="panel-body">
			{if $txt_empfaenger != ''}
				{ll key="leute_email_body1"}<br />
				&raquo;&nbsp;<a href="mailto:{$txt_empfaenger}"><b>{ll key="leute_email_all_recipients"}</b></a>
				<br />
				{if $txt_empfaenger_semicolon != ''}
					&raquo;&nbsp;<a href="mailto:{$txt_empfaenger_semicolon}"><b>{ll key="leute_email_all_recipients_semicolon"}</b></a>
					<br />
				{/if}
			{/if}
			<br />
			{if $tpl_ohne_email != ''}
				<strong><big>&middot;</big></strong>{ll key="leute_email_no_email"}&nbsp;&nbsp;
				{if $xls_filename}
					<a href="{$xls_filename}">{ll key="leute_email_xls_file"}</a>
				{/if}
				<br />
				{$tpl_ohne_email}
			{/if}
		</div>
	</div>

	<div class="panel panel-primary email-form">
		<div class="panel-heading">
			<h4 class="panel-title">{ll key="leute_email_title2"}</h4>
		</div>
		<div class="panel-body">
			<p>{ll key="leute_email_body2"}</p>
			<label for="leute_mailing_reply_to">{ll key="leute_email_reply_to"}</label>
			<select class="input-sm form-control" name="leute_mailing_reply_to" id="leute_mailing_reply_to">
				{foreach from=$tpl_reply_to_addresses item="replyTo"}
					<option value="{$replyTo.value}"{if $replyTo.value == $tpl_reply_to} selected="selected"{/if}>{$replyTo.desc}</option>
				{/foreach}
			</select><br>


			<label for="leute_mailing_subject">{ll key="leute_email_subject"} *</label>
			<input type="text" class="input-sm form-control" name="leute_mailing_subject" id="leute_mailing_subject" value="{$tpl_subject}"><br>

			<label for="leute_mailing_placeholders">{ll key="leute_email_placeholders"}</label>
			<select class="input-sm form-control" name="leute_mailing_placeholders" id="leute_mailing_placeholders" onchange="richtexteditor_insert_html('leute_mailing_text', $(this).val());$(this).val('');">
				{foreach from=$tpl_placeholders item="placeholder"}
					<option value="{$placeholder.value}">{$placeholder.desc}</option>
				{/foreach}
			</select><br>

			{if $tpl_sent_emails|@sizeof > 1}
				<label for="leute_mailing_text">{ll key="leute_email_sent_emails"}</label>
				<select class="input-sm form-control" name="leute_email_sent_emails" id="leute_email_sent_emails" onchange="if (!$(this).val()) return; c=confirm('{ll key="leute_email_confirm_replace_text"}'); if (!c) {ldelim}$(this).val('');return false;{rdelim} richtexteditor_set_html('leute_mailing_text', sentEmails[$(this).val()].text);$('#leute_mailing_subject').val(sentEmails[$(this).val()].subject);$(this).val('');">
					{foreach from=$tpl_sent_emails item="sentEmail"}
						<option value="{$sentEmail.value}" title="{$sentEmail.title}">{$sentEmail.desc}</option>
					{/foreach}
				</select><br>
			{/if}

			<label for="leute_mailing_text">{ll key="leute_email_text"}</label>
			<textarea name="leute_mailing_text" id="leute_mailing_text" class="richtexteditor">{$tpl_text}</textarea><br>

			<label for="leute_mailing_files">{ll key="leute_email_files"}</label>
			<div id="leute_mailing_files"></div>
			<input type="hidden" name="leute_mailing_files" value="{$tpl_files}">
			<script>
				$('#leute_mailing_files').fineUploader({ldelim}
				{foreach from=$tpl_fineuploader_labels item="label"}{$label}: "{ll key="fine_uploader_label_`$label`"}",
						{/foreach}debug: true,
						request: {ldelim}
					endpoint: '../inc/upload.php'
					{rdelim},
				thumbnails: {ldelim}
					placeholders: {ldelim}
						waitingPath: "../inc/fine-uploader/placeholders/waiting-generic.png",
								notAvailablePath: "../inc/fine-uploader/placeholders/not_available-generic.png"
						{rdelim}
					{rdelim},
				deleteFile: {ldelim}
					enabled: true,
							method: 'POST',
							endpoint: '/inc/upload.php'
					{rdelim},
				retry: {ldelim}
					enableAuto: true
					{rdelim},
				callbacks: {ldelim}
					onAllComplete: function(succeeded, failed) {ldelim}
						var v = [];
						this.getUploads({ldelim}status: qq.status.UPLOAD_SUCCESSFUL{rdelim}).forEach(function(e) {ldelim}
							v.push(e.uuid);
							{rdelim});
						$('[name="leute_mailing_files"]').val(v.join('@|,|@'));
						{rdelim},
					onDeleteComplete: function(id, xhr, isError) {ldelim}
						var v = [];
						this.getUploads({ldelim}status: qq.status.UPLOAD_SUCCESSFUL{rdelim}).forEach(function(e) {ldelim}
							v.push(e.uuid);
							{rdelim});
						$('[name="leute_mailing_files"]').val(v.join('@|,|@'));
						{rdelim}
					{rdelim}
				{rdelim});
				$('#leute_mailing_files').fineUploader('addInitialFiles', {$tpl_init_files});
				var sentEmails = {$tpl_sent_emails_json};
			</script>
		</div>
	</div>

	<div id="email-preview"></div>

	{if $crm_contact_tpl_groups}
		{assign var="tpl_groups" value=$crm_contact_tpl_groups}
		{assign var="tpl_hide_cancel" value=TRUE}
		{assign var="tpl_special_submit" value="&nbsp"}
		{include file="ko_formular.tpl"}
	{/if}
	<div class="btn-field email-form-send">
		{if $crm_contact_tpl_groups}
			<button class="btn btn-primary" type="submit" value="{ll key="save"}" name="submit" onclick="set_action('submit_email_contact_entry', this);this.submit();">{ll key="save"}&nbsp;<i class="fa fa-save"></i></button>
		{/if}
		<input type="hidden" name="ohne_email" value="{$tpl_ohne_email}" />
		<input type="hidden" name="res_ids" value="{$tpl_res_ids}" />
		<button class="btn btn-primary" type="submit" value="{if $crm_contact_tpl_groups}{ll key="leute_email_save_and_send"}{else}{ll key="send"}{/if}" name="submit_2" onclick="return mailingCheckSend(this, '{ll key="leute_email_error_no_subject"}', '{ll key="leute_email_error_no_replyto"}', '{ll key="leute_email_confirm_placeholders_nok"}');">
			{if $crm_contact_tpl_groups}
				{ll key="leute_email_save_and_send"}
			{else}
				{ll key="send"}
			{/if}
			&nbsp;<i class="fa fa-send"></i>
		</button}
	</div>

{/if}
