<table width="100%" cellspacing="0">
	<tr>
		<td class="subpart_header">
		{$label_title}
		</td>
		<td align="left" style="padding-left: 5px;">
			{if $help.show}{$help.link}{/if}
		</td>
		<td align="right">&nbsp;</td>
	</tr>

	<tr><td colspan="2" class="subpart">

		{if $show_invalid}
			<h2>{$label_title_invalid}</h2>

			<div>
				{$label_invalid_addresses}:<br />
				<div style="color: white; font-weight: 900; background: #00aa00; margin: 2px 0;" name="mailmerge_infobox"></div>
				<input type="image" src="{$ko_path}images/icon_export_my_list.png"  onclick="sendReq('../inc/ajax.php', 'action,sesid,ids', 'exporttomylist,{$sesid},{$invalid_addresses_ids}', do_element); return false;" title="{$label_export_to_mylist}" />
				&nbsp;
				{$invalid_addresses}
			</div>
		{/if}


			{if $show_reuse}
				<h2>{$label_title_reuse}</h2>

				<label class="formular_header">{$label_reuse_letter}:</label>
				<div>
					<select name="sel_layout" size="0" onchange="c=confirm('{$label_confirm_reuse}'); if(!c) return false; sendReq('../leute/inc/ajax.php', 'action,sesid,id', 'mailmergereuse,{$sesid},'+this.options[this.selectedIndex].value, mailmerge_reuse);">
					{foreach from=$letters_ids item=v key=k}
						<option value="{$v}">{$letters.$k}</option>
					{/foreach}
					</select>
				</div>
			{/if}


			<h2>{$label_title_new}</h2>

			<label class="formular_header">{$label_preset}:</label>
			<div>
				<select name="sel_layout" size="0">
				{foreach from=$layouts item=v}
					<option value="{$v}">{$v}</option>
				{/foreach}
				</select>
			</div>

			<label class="formular_header">{$label_opening}:</label>
			<div>
				<input type="radio" name="rd_salutation" value="informal" checked="checked" />{$label_opening_informal}&nbsp;&nbsp;
				<input type="radio" name="rd_salutation" value="formal" />{$label_opening_formal}
			</div>

			{if $show_sender}
				<label class="formular_header">{$label_sender}:</label>
				<div>
					<input type="radio" name="rd_sender" value="user" checked="checked" />{$sender_address_user}<br />
					<input type="radio" name="rd_sender" value="church" />{$sender_address_church}
				</div>
			{/if}

			<label class="formular_header">{$label_subject}:</label>
			<div>
				<input type="text" name="txt_subject" size="80" />
			</div>

			<label class="formular_header">{$label_text}:</label>
			<div>
				<div>
					<a href="javascript:add_markup(document.formular.txt_text, 'B');"><img src="{$ko_path}images/text_bold.png" border="0" title="Bold" /></a>
					<a href="javascript:add_markup(document.formular.txt_text, 'I');"><img src="{$ko_path}images/text_italic.png" border="0" title="Italic" /></a>
					&nbsp;
					<a href="javascript:add_markup(document.formular.txt_text, 'C');"><img src="{$ko_path}images/text_align_center.png" border="0" title="Center" /></a>
				</div>
				<table><tr><td>
					<textarea name="txt_text" cols="80" rows="20"></textarea>
					<img src="{$ko_path}images/icon_arrow_right.png" border="0" onclick="document.formular.txt_text.cols=document.formular.txt_text.cols+5;" title="{$label_enlarge_right}" />
					<div align="right" style="margin-right: 20px; margin-top: 2px;">
					<img src="{$ko_path}images/icon_arrow_down_big_enabled.png" border="0" onclick="document.formular.txt_text.rows=document.formular.txt_text.rows+5;" title="{$label_enlarge_down}" />
				</td></tr></table>
				</div>
			</div>

			<label class="formular_header">{$label_closing}:</label>
			<div>
				<input type="text" name="txt_closing" size="80" />
			</div>

			<label class="formular_header">{$label_signature}:</label>
			<div>
				<input type="text" name="txt_signature" size="80" value="{$signature}" />
			</div>

			<label class="formular_header">{$label_sig_file}:</label>
			<div>
				{if $show_sig_file}
					<input type="checkbox" name="chk_sig_file" checked="checked" />{$label_chk_sig_file}<br /><br />
				{/if}
				<input type="file" name="file_sig_file" size="50" value="" />
			</div>


		<h2>{$label_title_legend}</h2>
		<table border="0">
			{foreach from=$colLegend item=mark key=k}
				<tr>
					<td>{$mark}</td>
					<td>###COL_{$k}###</td>
				</tr>
			{/foreach}
		</table>

	</td></tr>
</table>


<p align="center">
<input type="submit" value="{$label_submit}" name="submit" onclick="set_action('submit_mailmerge', this);" />
&nbsp;&nbsp;&nbsp;
<input type="submit" value="{$label_cancel}" name="cancel" onclick="set_action('{$tpl_cancel}', this);" />
</p>
