{if !$tpl_hide_header}
	<h3>{$label_title}{if $help.show} {$help.link}{/if}</h3>
{/if}

{if $tpl_export_warning}
	<div class="alert alert-danger" role="alert" id="leute-warning-export">{$tpl_export_warning}</div>
{/if}

{if $show_invalid}
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title">
			{$label_title_invalid}
		</h4>
	</div>
	<div class="panel-body">
		{$label_invalid_addresses}:<br />
		<div style="color: white; font-weight: 900; background: #00aa00; margin: 2px 0;" name="mailmerge_infobox"></div>
		<input type="image" src="{$ko_path}images/icon_export_my_list.png"  onclick="sendReq('../inc/ajax.php', 'action,sesid,ids', 'exporttomylist,{$sesid},{$invalid_addresses_ids}', do_element); return false;" title="{$label_export_to_mylist}" />
		&nbsp;
		{$invalid_addresses}
	</div>
</div>
{/if}

{if $show_reuse}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				{$label_title_reuse}
			</h4>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="formular-cell col-sm-6">
					<div class="formular_header">
						<label for="sel_preset">{$label_reuse_letter}</label>
					</div>
					<div class="formular_content">
						<select name="sel_preset" class="input-sm form-control" onchange="c=confirm('{$label_confirm_reuse}'); if(!c) return false; sendReq('../leute/inc/ajax.php', 'action,sesid,id', 'mailmergereuse,{$sesid},'+this.options[this.selectedIndex].value, mailmerge_reuse);">
							{foreach from=$letters_ids item=v key=k}
								<option value="{$v}">{$letters.$k}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
{/if}


<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title">
			{$label_title_new}
		</h4>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="formular-cell col-sm-6">
				<div class="formular_header">
					<label for="sel_layout">{$label_preset}</label>
				</div>
				<div class="formular_content">
					<select name="sel_layout" class="input-sm form-control">
						{foreach from=$layouts item=v}
							<option value="{$v}">{ll key="mailmerge_layout_`$v`"}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="formular-cell col-sm-6">
				<div class="formular_header">
					<label>{$label_opening}</label>
				</div>
				<div class="formular_content">
					<input type="radio" name="rd_salutation" value="informal" checked="checked">{$label_opening_informal}&nbsp;&nbsp;
					<input type="radio" name="rd_salutation" value="formal">{$label_opening_formal}
				</div>
			</div>
		</div>

		{if $sender_address_user || $sender_address_church}
			<div class="row">
				<div class="formular-cell col-sm-6">
					<div class="formular_header">
						<label>{$label_sender}</label>
					</div>
					<div class="formular_content">
						<input type="radio" name="rd_sender" value="none" checked="checked">{ll key="leute_mailmerge_label_sender_none"}<br />
						{if $sender_address_user}
							<input type="radio" name="rd_sender" value="user">{$sender_address_user}<br />
							<input type="radio" name="rd_sender" value="user_pp">P.P. {$sender_address_user}<br />
						{/if}
						{if $sender_address_church}
							<input type="radio" name="rd_sender" value="church">{$sender_address_church}<br />
							<input type="radio" name="rd_sender" value="church_pp">P.P. {$sender_address_church}<br />
						{/if}
					</div>
				</div>
			</div>
		{/if}

		<div class="row">
			<div class="formular-cell col-sm-6">
				<div class="formular_header">
					<label for="txt_subject">{$label_subject}</label>
				</div>
				<div class="formular_content">
					<input type="text" class="input-sm form-control" name="txt_subject">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="formular-cell col-sm-12">
				<div class="formular_header">
					<label for="txt_text">{$label_text}</label>
				</div>
				<div class="formular_content">
					<textarea name="txt_text" class="richtexteditor"></textarea>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="formular-cell col-sm-6">
				<div class="formular_header">
					<label for="txt_closing">{$label_closing}</label>
				</div>
				<div class="formular_content">
					<input type="text" name="txt_closing" class="input-sm form-control">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="formular-cell col-sm-6">
				<div class="formular_header">
					<label for="txt_signature">{$label_signature}</label>
				</div>
				<div class="formular_content">
					<input type="text" name="txt_signature" class="input-sm form-control" value="{$signature}">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="formular-cell col-sm-6">
				<div class="formular_header">
					<label for="file_sig_file">{$label_sig_file}</label>
				</div>
				<div class="formular_content">
					{if $show_sig_file}
						<input type="checkbox" name="chk_sig_file" checked="checked">{$label_chk_sig_file}<br /><br />
					{/if}
					<input type="file" name="file_sig_file" size="50" value="">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="formular-cell col-sm-6">
				<div class="formular_header">
					<label>{$label_title_legend}</label>
				</div>
				<table border="0">
					{foreach from=$colLegend item=mark key=k}
						<tr>
							<td>{$mark}</td>
							<td>###COL_{$k}###</td>
						</tr>
					{/foreach}
				</table>
			</div>
		</div>

	</div>
</div>



<div class="btn-field">
<button type="submit" class="btn btn-primary" value="{$label_submit}" name="submit" onclick="set_action('submit_mailmerge', this);">{$label_submit}</button>
<button type="submit" class="btn btn-danger" value="{$label_cancel}" name="cancel" onclick="set_action('{$tpl_cancel}', this);">{$label_cancel}</button>
</div>
