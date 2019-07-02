{if $tpl_list_title}
	<div class="list-title" style="{$tpl_list_title_styles}">{$tpl_list_title}</div>
{/if}

{if $tpl_list_subtitle}
	<div class="list-subtitle">{$tpl_list_subtitle}</div>
{/if}

{if !$tpl_hide_header}
	<!-- help icon -->
	{if $help.show}
		<div class="list-help">
			{$help.link}
		</div>
	{/if}

	<!-- multisort -->
	{if $multisort.show}
		<div align="left" class="multisort">
			<span onclick="change_vis('multiSort');">
				<a href="#"><img src="{$ko_path}/images/multisort.png" border="0" alt="multisort" title="multisort" />&nbsp;{$multisort.showLink}</a>
			</span>
		</div>
	{/if}

	<!-- paging stats -->
	<div align="right" class="pagestats">
	{$tpl_stats}
	{if !$hide_listlimiticons}
		<img src="{$ko_path}/images/decrease.png" border="0" alt="-" style="margin-bottom: -3px; cursor: pointer;" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', '{if $limitAction != ''}{$limitAction}{else}setstart{/if},{$limitM},{$sesid}', do_element);" />
		<img src="{$ko_path}/images/increase.png" border="0" alt="-" style="margin-bottom: -3px; cursor: pointer;" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', '{if $limitAction != ''}{$limitAction}{else}setstart{/if},{$limitP},{$sesid}', do_element);" />
	{/if}
	&nbsp;&nbsp;

	<!-- paging page select -->
	{if $show_page_select}
		{$show_page_select_label}: 
		<select name="sel_list_page" size="0" onchange="sendReq('../{$module}/inc/ajax.php', 'action,set_start,sesid', 'setstart,'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
		{foreach from=$show_page_values item=v key=k}
			<option value="{$v}" {if $v == $show_page_selected}selected="selected"{/if}>{$show_page_output.$k}</option>
		{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;
	{/if}


	<!-- paging navigation -->
	{if $tpl_prevlink_link neq ""}
		<a href="{$tpl_prevlink_link}">
		<img src="{$ko_path}images/icon_arrow_left.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
		</a>
	{else}
		<img src="{$ko_path}images/icon_arrow_left_disabled.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
	{/if}

	{if $tpl_nextlink_link neq ""}
		<a href="{$tpl_nextlink_link}">
		<img src="{$ko_path}images/icon_arrow_right.png" border="0" alt="{$label_list_next}" title="{$label_list_next}" />
		</a>
	{else}
		<img src="{$ko_path}images/icon_arrow_right_disabled.png" border="0" alt="{$label_list_next}" title="{$label_list_next}" />
	{/if}
	&nbsp;

	</div>
	<br clear="all" />
{/if}


<!-- multi sorting -->
{if $multisort.show}
	<div id="multiSort" {if !$multisort.open}style="visibility: hidden; display: none;"{/if} style="margin-left: 25px;">
		{foreach from=$multisort.columns item=i}
			{$i+1}.
			<select name="sel_multisort_{$i}" size="0" onchange="sendReq('../{$module}/inc/ajax.php', 'action,col,sort,sesid', 'setmultisort,{$i},'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
			{foreach from=$multisort.select_values item=v key=k}
				<option value="{$v}" {if $v == $multisort.select_selected.$i}selected="selected"{/if}>{$multisort.select_descs.$k}</option>
			{/foreach}
			</select>
			<span onclick="sendReq('../{$module}/inc/ajax.php', 'action,col,order,sesid', 'setmultisort,{$i},{if $multisort.order.$i == "DESC"}ASC{else}DESC{/if},{$sesid}', do_element);">
			<img src="{$ko_path}images/icon_arrow_{if $multisort.order.$i == "ASC"}up{else}down{/if}_enabled.gif" alt="sort" title="{if $multisort.order.$i == "DESC"}{$label_list_sort_asc}{else}{$label_list_sort_desc}{/if}" />
			</span>
			&ensp;
		{/foreach}

		{assign var="i" value=$i+1}
		{$i+1}.
		<select name="sel_multisort_{$i}" size="0" onchange="sendReq('../{$module}/inc/ajax.php', 'action,col,sort,sesid', 'setmultisort,{$i},'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
		{foreach from=$multisort.select_values item=v key=k}
			<option value="{$v}">{$multisort.select_descs.$k}</option>
		{/foreach}
		</select>

	</div>
{/if}


<table width="100%" class="ko_list" cellpadding="0" cellspacing="0">
<tr>
{if $overlay}
	<th class="ko_list list-check">
	<img src="{$ko_path}images/icon_checked.gif" border="0" width="13" height="13" title="{$label_list_check}" alt="{$label_list_check}" onclick="select_all_list_chk();{$checkbox_all_code}" />
	</th>
	{if $tpl_show_4cols_leute}
		<th class="ko_list list-family">
		<img src="{$ko_path}images/icon_familie.png" border="0" width="16" height="16" title="{$label_list_check_family}" alt="{$label_ist_check_family}" onclick="select_all_fam_chk();" />
		</th>
	{/if}
	<th class="ko_list list-edit"></th>
{else}
	{if $tpl_show_3cols || $tpl_show_4cols_leute}
		<th class="ko_list list-check">
		<img src="{$ko_path}images/icon_checked.gif" border="0" width="13" height="13" title="{$label_list_check}" alt="{$label_list_check}" onclick="select_all_list_chk();{$checkbox_all_code}" />
		</th>
		{if $tpl_show_4cols_leute}
			<th class="ko_list list-family">
			<img src="{$ko_path}images/icon_familie.png" border="0" width="16" height="16" title="{$label_list_check_family}" alt="{$label_list_check_family}" onclick="select_all_fam_chk();" />
			</th>
		{/if}
		{if $tpl_show_version_col}
			<th class="ko_list list-version"></th>
		{/if}
		{if $tpl_show_tracking_col}
			<th class="ko_list list-tracking"></th>
		{/if}
		{if $tpl_show_mailing_col}
			<th class="ko_list list-mailing"></th>
		{/if}
		<th class="ko_list list-edit"></th>
		<th class="ko_list list-delete"></th>
	{/if}
{/if}
{foreach from=$tpl_table_header item=h}
	<th class="{if $h.class}{$h.class}{else}ko_list{/if}" {if $h.id}id="{$h.id}"{/if} {if $h.title}title="{$h.title}"{/if}>
	<span style="white-space:nowrap; position: relative;">
	{if $sort.show && $h.sort != ""}
		<span class="list-sorticon">
		{assign var="type" value="disabled"}
		{if $sort.akt == $h.sort AND $sort.akt_order == "DESC"} {assign var="type" value="enabled"} {/if}
		<a href="javascript:sendReq('../{$module}/inc/ajax.php', 'action,sort,sort_order,sesid', '{$sort.action},{$h.sort},DESC,{$sesid}', do_element);">
		<img src="{$ko_path}images/icon_arrow_down_{$type}.gif" alt="sort" border="0" title="{$label_list_sort_desc}" />
		</a>
		</span>
	{/if}

	{$h.name}

	{if $sort.show && $h.sort != ""}
		<span class="list-sorticon">
		{assign var="type" value="disabled"}
		{if $sort.akt == $h.sort AND $sort.akt_order == "ASC"} {assign var="type" value="enabled"} {/if}
		<a href="javascript:sendReq('../{$module}/inc/ajax.php', 'action,sort,sort_order,sesid', '{$sort.action},{$h.sort},ASC,{$sesid}', do_element);">
		<img src="{$ko_path}images/icon_arrow_up_{$type}.gif" alt="sort" border="0" title="{$label_list_sort_asc}" />
		</a>
		</span>
	{/if}
	</span>

	</th>
{/foreach}
</tr>


{foreach from=$tpl_list_data item=l}
	<tr class="{cycle values="ko_list_even,ko_list_odd"} {$l.rowclass}" onmousedown="{$l.rowclick_code}">
	{if $tpl_show_3cols || $tpl_show_4cols_leute}
		<td width="20" class="list-check">
		{if $l.show_checkbox}
			<input type="checkbox" name="chk[{$l.id}]" id="chk[{$l.id}]" onclick="{$checkbox_code}" title="id: {$l.id}" />
		{elseif $l.show_numberfield}
			<input type="text" onclick="return false;" name="txt[{$l.id}]" size="{$l.numberfield_size}" maxlength="{$l.numberfield_size}" />
		{else}
			&nbsp;
		{/if}
		</td>
	{/if}

	{if $tpl_show_4cols_leute}
		<td width="20" class="list-family">
		{if $l.show_fam_checkbox}
			<input type="checkbox" name="famchk[{$l.famid}]" />
		{else}
			&nbsp;
		{/if}
		</td>
	{/if}

	{if $overlay}
		<td width="20" class="list-edit-overlay" onmouseover="$('#overlay_{$l.id}').show();" onmouseout="$('#overlay_{$l.id}').hide();">
		{if $l.show_edit_button || $l.show_delete_button || $tpl_show_version_col}
			<div style="position: relative;">
				<input type="image" src="{$ko_path}images/button_edit_overlay.gif" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}" />
				<div class="list_overlay" id="overlay_{$l.id}" onmouseout="$(this).hide();">
					{if $l.show_edit_button}
						<input type="image" src="{$ko_path}images/button_edit.gif" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}" />
					{/if}

					{if $l.show_delete_button}
						<input type="image" src="{$ko_path}images/button_delete.gif" alt="{$l.alt_delete}" title="{$l.alt_delete}" onclick="{$l.onclick_delete}" />
					{/if}

					{if $tpl_show_version_col}
						<input type="image" src="{$ko_path}images/button_version.png" alt="{$l.alt_version}" title="{$l.alt_version}" onclick="{$l.onclick_version}" />
					{/if}

					{if $tpl_show_clipboard}
						<span class="clipboardContainer" data-clipboard-text="{$l.clipboard_content}" title="{$label_clipboard}">
							<img src="{$ko_path}images/icon_clipboard.png" />
						</span>
					{/if}

					{if $tpl_show_maps_link && $l.maps_link != ''}
						<a href="{$l.maps_link}" target="_blank">
							<img src="{$ko_path}images/google_maps.png" border="0" alt="Google Maps" title="{$label_google_maps}" />
						</a>
					{/if}

					<a href="javascript:ko_image_popup('{$ko_path}inc/qrcode.php?s={$l.qrcode_string}&h={$l.qrcode_hash}&size=7');"><img src="{$ko_path}images/icon_qrcode.png" title="{$label_qrcode}" /></a>
					
					{if $tpl_show_word}
						<a href="#" onclick="sendReq('../{$module}/inc/ajax.php', 'action,pid,sesid', 'addressdoc,{$l.id},{$sesid}', do_element);"><img src="{$ko_path}images/icon_word.png" title="{$label_word}" /></a>
					{/if}

				</div>
			</div>
		{/if}
		</td>

	{else}

		{if $tpl_show_3cols || $tpl_show_4cols_leute}
			<td width="20" class="list-edit">
			{if $l.show_edit_button}
				<input type="image" src="{$ko_path}images/button_edit.gif" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}" />
			{elseif $l.show_check_button}
				<input type="image" src="{$ko_path}images/button_check.png" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}" />
			{elseif $l.show_send_button}
				<input type="image" src="{$ko_path}images/forward.gif" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}" />
			{elseif $l.show_undelete_button}
				<input type="image" src="{$ko_path}images/undelete.png" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}" />
			{/if}
			</td>
		{/if}

		{if $tpl_show_3cols || $tpl_show_4cols_leute}
			<td width="20" class="list-delete">
			{if $l.show_delete_button}
				<input type="image" src="{$ko_path}images/button_delete.gif" alt="{$l.alt_delete}" title="{$l.alt_delete}" onclick="{$l.onclick_delete}" />
			{/if}
			</td>
		{/if}

		{if $tpl_show_version_col}
			<td width="20" class="list-version">
			<input type="image" src="{$ko_path}images/button_version.png" alt="{$l.alt_version}" title="{$l.alt_version}" onclick="{$l.onclick_version}" />
			</td>
		{/if}

		{if $tpl_show_tracking_col}
			<td width="20" class="list-tracking">
			{if $l.tracking_mode}
				<input type="image" src="{$ko_path}images/tracking_{$l.tracking_mode}.png" alt="{$l.alt_tracking}" title="{$l.alt_tracking}" onclick="{$l.onclick_tracking}" />
			{/if}
			</td>
		{/if}

		{if $tpl_show_mailing_col}
			<td width="20" class="list-mailing">
				{if $l.mailing_link != ''}
					<a href="mailto:{$l.mailing_link}"><img src="{$ko_path}images/send_email.png" alt="@" title="{$l.alt_mailing}" border="0" /></a>
				{else}
					&nbsp;
				{/if}
			</td>
		{/if}

	{/if}

	{foreach from=$tpl_list_cols item=c}
		{assign var="t" value=$tpl_edit_columns.$c}
		<td {if $t == "telp" || $t == "telg" || $t == "natel" || $t == "fax"}style="white-space: nowrap;"{/if} id="{$db_table}|{$l.id}|{$db_cols.$c}">
		{$l.$c}
		</td>
	{/foreach}

	</tr>
	{if $tpl_show_version_col}
		<tr style="display: none;" name="version_tr_{$l.id}" id="version_tr_{$l.id}">
			<td colspan="{$version_colspan}">
				<div name="version_{$l.id}" id="version_{$l.id}"></div>
			</td>
		</tr>
	{/if}
{/foreach}

{if $tpl_show_editrow}
	<tr class="ko_list_footer">
	{if $overlay}
		{assign var="colspan" value=2}
		{if $tpl_show_4cols_leute}{assign var="colspan" value=$colspan+1}{/if}
		<th class="ko_list_footer" align="left" colspan="{$colspan}">
		<img src="{$ko_path}images/icon_checked.gif" border="0" width="13" height="13" alt="check" onclick="select_all_list_chk();{$checkbox_all_code}" />
		</th>
	{else}
		{if $tpl_show_3cols}
			{assign var="colspan" value=3}
			{if $tpl_show_version_col}{assign var="colspan" value=$colspan+1}{/if}
			{if $tpl_show_4cols_leute}{assign var="colspan" value=$colspan+1}{/if}
			<th class="ko_list_footer" colspan="{$colspan}" align="left">
		{else}
			<th class="ko_list_footer" align="left">
		{/if}
		<img src="{$ko_path}images/icon_checked.gif" border="0" width="13" height="13" alt="check" onclick="select_all_list_chk();{$checkbox_all_code}" />
		</th>
		{if $tpl_show_tracking_col}
			<th class="ko_list_footer" align="left"></th>
		{/if}
		{if $tpl_show_mailing_col}
			<th class="ko_list_footer" align="left"></th>
		{/if}
	{/if}
	{foreach from=$tpl_edit_columns item=c}
		<th class="ko_list_footer" align="right">
			{if $c != ""}
				<input type="image" src="{$ko_path}images/button_edit.gif" alt="{$multiedit_list_title}" title="{$multiedit_list_title}" onclick="set_action('multiedit', this);set_hidden_value('id', '{$c}', this);" />
			{else}
				&nbsp;
			{/if}
		</th>
	{/foreach}
	</tr>
{/if}


{if $tpl_show_sort_cols}
	<tr class="ko_list_footer">
	{if $overlay}
		<th class="ko_list_footer" align="left" colspan="2">
		<img src="{$ko_path}images/icon_checked.gif" border="0" width="13" height="13" alt="check" onclick="select_all_list_chk();{$checkbox_all_code}" />
		</th>
	{else}
		{if $tpl_show_3cols}
			{assign var="colspan" value=3}
			{if $tpl_show_version_col}{assign var="colspan" value=$colspan+1}{/if}
			{if $tpl_show_4cols_leute}{assign var="colspan" value=$colspan+1}{/if}
			<th class="ko_list_footer" colspan="{$colspan}" align="left">
		{else}
			<th class="ko_list_footer" align="left">
		{/if}
		&nbsp;
		</th>
	{/if}
	{foreach from=$tpl_sort_cols item=c}
		<th class="ko_list_footer" align="center">
			{if $c != ""}
				<img src="{$ko_path}images/icon_arrow_left.png" alt="left" title="{$label_list_col_left}" onclick="sendReq('../{$module}/inc/ajax.php', 'action,col,sesid', 'movecolleft,{$c},{$sesid}', do_element);" />
				<img src="{$ko_path}images/icon_arrow_right.png" alt="right" title="{$label_list_col_right}" onclick="sendReq('../{$module}/inc/ajax.php', 'action,col,sesid', 'movecolright,{$c},{$sesid}', do_element);" />
			{else}
				&nbsp;
			{/if}
		</th>
	{/foreach}
	</tr>
{/if}
</table>


{if !$tpl_hide_header}
	<div align="right" class="pagestats" style="padding-top: 2px;">
	{$tpl_stats}
	&nbsp;&nbsp;&nbsp;

	{if $show_page_select}
		{$show_page_select_label}: 
		<select name="sel_list_page" size="0" onchange="sendReq('../{$module}/inc/ajax.php', 'action,set_start,sesid', 'setstart,'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
		{foreach from=$show_page_values item=v key=k}
			<option value="{$v}" {if $v == $show_page_selected}selected="selected"{/if}>{$show_page_output.$k}</option>
		{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;
	{/if}

	{if $tpl_prevlink_link neq ""}
		<a href="{$tpl_prevlink_link}">
		<img src="{$ko_path}images/icon_arrow_left.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
		</a>
	{else}
		<img src="{$ko_path}images/icon_arrow_left_disabled.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
	{/if}

	{if $tpl_nextlink_link neq ""}
		<a href="{$tpl_nextlink_link}">
		<img src="{$ko_path}images/icon_arrow_right.png" border="0" alt="{$label_list_next}" title="{$label_list_next}" />
		</a>
	{else}
		<img src="{$ko_path}images/icon_arrow_right_disabled.png" border="0" alt="{$label_list_next}" title="{$label_list_next}" />
	{/if}
	&nbsp;

	</div>
{/if}


{if $show_list_footer}
	<table style="margin-left:12px" cellspacing="0" cellpadding="3" class="list-footer">
	<tr><td style="border-left-style:solid;border-left-width:1px">&nbsp;</td></tr>
	
	{foreach from=$list_footer item=footer}
		<tr><td style="border-left-style:solid;border-left-width:1px;border-bottom-width:1px;border-bottom-style:solid;">
		&nbsp;{$footer.label}
		&nbsp;&nbsp;{$footer.button}
		</td></tr>
	{/foreach}

	</table>
{/if}
