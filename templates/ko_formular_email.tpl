{if $tpl_show_rec_link && ($txt_empfaenger != '' || $tpl_ohne_email != '')}
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title">
				{$tpl_title1}
			</h4>
		</div>
		<div class="panel-body">
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
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title">
				{$tpl_title2}
			</h4>
		</div>
		<div class="panel-body">
			<p>{$tpl_body2}</p>
			<div class="form-horizontal">
				<div class="form-group">
					<label for="inputEmail3" class="col-sm-2 control-label">{if $tpl_show_additional_rec}{$tpl_more} {/if}{$tpl_to}</label>
					<div class="col-sm-10">
						<input type="text" class="form-control input-sm" name="txt_empfaenger" value="{$txt_empfaenger}"{if $to_readonly} readonly="readonly"{/if}>
						<!--<img src="{$ko_path}images/icon_exchange_comma.gif" border="0" onclick="javascript:exchangeComma(document.getElementsByName('txt_empfaenger')[0]);" />
						{if $tpl_show_to_bcc}
							&nbsp;
							<img src="{$ko_path}images/icon_to_bcc.gif" border="0" onclick="javascript:to=document.getElementsByName('txt_empfaenger')[0]; bcc=document.getElementsByName('txt_bcc')[0];bcc.value=bcc.value+(bcc.value?',':'')+to.value;to.value='{$tpl_info_email}';" />
						{/if}-->
					</div>
				</div>
				<div class="form-group">
					<label for="inputEmail3" class="col-sm-2 control-label">{$tpl_cc}</label>
					<div class="col-sm-10">
						<input class="input-sm form-control" type="text" name="txt_cc" value="{$txt_cc}"{if $cc_readonly} readonly="readonly"{/if}>
					</div>
				</div>
				<div class="form-group">
					<label for="inputEmail3" class="col-sm-2 control-label">{$tpl_bcc}</label>
					<div class="col-sm-10">
						<input type="text" name="txt_bcc" class="form-control input-sm" value="{$txt_bcc}"{if $bcc_readonly} readonly="readonly"{/if}>
					</div>
				</div>
				<div class="form-group">
					<label for="inputEmail3" class="col-sm-2 control-label">{$tpl_subject} *</label>
					<div class="col-sm-10">
						<input type="text" name="txt_betreff" class="input-sm form-control" value="{$txt_betreff}" />
					</div>
				</div>

				<div class="form-group">
					<label for="inputEmail3" class="col-sm-2 control-label">{$tpl_text}</label>
					<div class="col-sm-10">
						<textarea cols="80" rows="10" name="txt_emailtext" class="input-sm form-control" style="font-family: monospace;">{$txt_emailtext}</textarea>
					</div>
				</div>
				{if $tpl_show_bcc_an_mich}
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">{$tpl_bcc_me}</label>
						<div class="col-sm-10">
							<div class="radio">
								<label>
									<input type="radio" name="rd_bcc_an_mich" value="ja">{$tpl_yes}
								</label>
								&nbsp;&nbsp;
								<label>
									<input type="radio" name="rd_bcc_an_mich" value="nein" checked="checked">{$tpl_no}
								</label>
							</div>
						</div>
					</div>
				{/if}
			</div>
		</div>
	</div>

	{if $crm_contact_tpl_groups}
		{assign var="tpl_groups" value=$crm_contact_tpl_groups}
		{assign var="tpl_hide_cancel" value=TRUE}
		{assign var="tpl_special_submit" value="&nbsp"}
		{include file="ko_formular.tpl"}
	{/if}
	<div class="btn-field">
		{if $crm_contact_tpl_groups}
			<button class="btn btn-primary" type="submit" value="{ll key="save"}" name="submit" onclick="if(ko_validate_email_form() === false) {literal}{{/literal} return false; {literal}} else {{/literal} set_action('submit_email_contact_entry', this);this.submit; {literal}}{/literal}">{ll key="save"}&nbsp;<i class="fa fa-save"></i></button>
		{/if}

		{if $tpl_show_send}
			<input type="hidden" name="ohne_email" value="{$tpl_ohne_email}" />
			<input type="hidden" name="res_ids" value="{$tpl_res_ids}" />
			<button class="btn btn-primary" type="submit" value="{if $crm_contact_tpl_groups}{ll key="leute_email_save_and_send"}{else}{ll key="send"}{/if}" name="submit_2" onclick="if(ko_validate_email_form() === false) {literal}{{/literal} return false; {literal}} else {{/literal} set_action('submit_email', this);this.submit; {literal}}{/literal}">{if $crm_contact_tpl_groups}{ll key="leute_email_save_and_send"}{else}{ll key="send"}{/if}&nbsp;<i class="fa fa-send"></i></button>
		{/if}
	</div>

{/if}


