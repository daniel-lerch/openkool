<h3 class="ko_list_title">
	{if !$tpl_hide_header && $help.show}
		<span class="pull-left help-icon">&nbsp;{$help.link}</span>
	{/if}
	<span class="pull-left">
		{$tpl_list_title}
		{if $tpl_list_subtitle}
			&nbsp;
			<small>
				{$tpl_list_subtitle}
			</small>
		{/if}
	</span>

	{if $tpl_list_link_new}
		<span class="pull-left list-add-new">
			<a href="{$tpl_list_link_new}"><i class="fa fa-plus"></i></a>
		</span>
	{/if}

	<!-- multisort -->
	{if $multisort.show}
		<div class="multisort pull-left">
			<button type="button" class="btn btn-sm btn-link" alt="multisort" title="multisort" data-toggle="collapse" data-target="#multiSortContainer">
				<i class="fa fa-sort"></i>
				&nbsp;{$multisort.showLink}
			</button>
		</div>
	{/if}

	{if !$tpl_hide_header}
		<div class="pagestats pull-right">
			<div class="btn-toolbar">

				{if $show_colitemlist}
					<div class="btn-group-sm">
						<div id="ko_list_colitemlist">
							<div id="ko_list_colitemlist_click"></div>
							<div id="ko_list_colitemlist_flyout">
								{$colitemlist}
							</div>
						</div>
					</div>
				{/if}

				<div class="btn-group btn-group-sm" role="group">
					<button class="btn btn-default" disabled>{$tpl_stats}</button>
				</div>

				{if !$stats.hide_listlimiticons}
					<div class="btn-group btn-group-sm limiting-box" role="group">
						<button type="button" class="btn btn-default" alt="-" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', '{if $limitAction != ''}{$limitAction}{else}setstart{/if},{$limitM},{$sesid}', do_element);return false;">
							<span class="glyphicon glyphicon-minus"></span>
						</button>
						<button type="button" class="btn btn-default" alt="+" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', '{if $limitAction != ''}{$limitAction}{else}setstart{/if},{$limitP},{$sesid}', do_element);return false;">
							<span class="glyphicon glyphicon-plus"></span>
						</button>
					</div>
				{/if}

				{if $show_page_select}
					<div class="input-group input-group-sm page-select-box">
						<div class="input-group-btn auto-width">
							<button class="btn btn-sm btn-default" disabled>{$show_page_select_label}</button>
						</div>
					<select class="input-sm" name="sel_list_page" size="0" onchange="sendReq('../{$module}/inc/ajax.php', 'action,set_start,sesid', 'setstart,'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
						{foreach from=$show_page_values item=v key=k}
							<option value="{$v}" {if $v == $show_page_selected}selected="selected"{/if}>{$show_page_output.$k}</option>
						{/foreach}
					</select>
					</div>
				{/if}

				<div class="btn-group btn-group-sm paging-box">
					<button class="btn btn-default" alt="{$label_list_back}" title="{$label_list_back}"{if $tpl_prevlink_link neq ""} onclick="{$tpl_prevlink_link};return false;"{else} disabled{/if}>
						<i class="fa fa-arrow-left"></i>
					</button>
					<button class="btn btn-default" alt="{$label_list_next}" title="{$label_list_next}"{if $tpl_nextlink_link neq ""} onclick="{$tpl_nextlink_link};return false;"{else} disabled{/if}>
						<i class="fa fa-arrow-right"></i>
					</button>
				</div>

			</div>
		</div>
		<br clear="all" />

	{/if}
</h3>



<!-- multi sorting -->
{if $multisort.show}
	<div class="collapse{if $multisort.open} in{/if}" id="multiSortContainer">
		<div class="well" id="multiSort">
			<div class="row">
				{assign var="mscCounter" value=0}
				{foreach from=$multisort.columns item=i}
					{if $mscCounter % 12 == 0}
						</div>
						<div class="row">
					{/if}
					<div class="col-xs-12 col-sm-6 col-md-3">
						<div class="input-group input-group-sm">
							<span class="input-group-addon">{$i+1}.</span>
							<select name="sel_multisort_{$i}" class="input-sm form-control" onchange="sendReq('../{$module}/inc/ajax.php', 'action,col,sort,sesid', 'setmultisort,{$i},'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
								{foreach from=$multisort.select_values item=v key=k}
									<option value="{$v}" {if $v == $multisort.select_selected.$i}selected="selected"{/if}>{$multisort.select_descs.$k}</option>
								{/foreach}
							</select>
							<div class="input-group-btn">
								<button type="button" class="btn btn-default" onclick="sendReq('../{$module}/inc/ajax.php', 'action,col,order,sesid', 'setmultisort,{$i},{if $multisort.order.$i == "DESC"}ASC{else}DESC{/if},{$sesid}', do_element);" alt="sort" title="{if $multisort.order.$i == "DESC"}{$label_list_sort_asc}{else}{$label_list_sort_desc}{/if}">
									<i class="fa {if $multisort.order.$i == "ASC"}fa-sort-asc{else}fa-sort-desc{/if}"></i>
								</button>
							</div>
						</div>
					</div>
					{assign var="mscCounter" value=$mscCounter+3}
				{/foreach}
				{assign var="i" value=$i+1}
				{if $mscCounter % 12 == 0}
					</div>
					<div class="row">
				{/if}
				<div class="col-xs-12 col-sm-6 col-md-3">
					<div class="input-group input-group-sm">
						<span class="input-group-addon">{$i+1}.</span>
						<select name="sel_multisort_{$i}" class="input-sm form-control" onchange="sendReq('../{$module}/inc/ajax.php', 'action,col,sort,sesid', 'setmultisort,{$i},'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
							{foreach from=$multisort.select_values item=v key=k}
								<option value="{$v}" {if $v == $multisort.select_selected.$i}selected="selected"{/if}>{$multisort.select_descs.$k}</option>
							{/foreach}
						</select>
						<div class="input-group-btn">
							<button type="button" class="btn btn-default" onclick="sendReq('../{$module}/inc/ajax.php', 'action,col,order,sesid', 'setmultisort,{$i},{if $multisort.order.$i == "DESC"}ASC{else}DESC{/if},{$sesid}', do_element);" alt="sort" title="{if $multisort.order.$i == "DESC"}{$label_list_sort_asc}{else}{$label_list_sort_desc}{/if}">
								<i class="fa {if $multisort.order.$i == "ASC"}fa-sort-asc{else}fa-sort-desc{/if}"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/if}



<table class="ko_list table table-condensed table-alternating table-bordered table-hover tablesaw tablesaw-stack" data-tablesaw-mode="stack">
	<thead>
	<tr class="row-info no-hover">
		<th class="ko_list list-check notdraggable dragtable-drag-boundary" style="width:auto;">

		{foreach from=$show_control_cols key='k' item='control_col'}
			{if $k=='check'}
				<button type="button" class="icon" onclick="select_all_list_chk(this);{$checkbox_all_code};" title="{$label_list_check}" alt="{$label_list_check}"><i class="fa fa-check-square-o list-icon"></i></button>
			{elseif $k=='family'}
				<a href="#" title="{$label_list_check_family}" alt="{$label_list_check_family}" onclick="select_all_fam_chk();return false;"><i class="fa fa-users list-icon"></i></a>
			{/if}
		{/foreach}
		</th>
		
		
		
		{if $show_overlay}
			<th class="ko_list list-overlay-field"></th>
		{/if}
		{foreach from=$tpl_table_header item=h}
			<th style="position:relative;" class="{if $h.class}{$h.class}{else}ko_list{/if}{if $sort.show} ko_listh_sorting{/if}" {if $h.id}id="{$h.id}"{/if} {if $h.title}title="{$h.title}"{/if}{if $sort.show} data-toggle="popover" data-placement="bottom" data-title="{ll key="list_sorting"}" data-content="
			<div class=&quot;btn-group btn-group-sm&quot;>
				<button type=&quot;button&quot; class=&quot;btn btn-default&quot; {if $sort.akt == $h.sort AND $sort.akt_order == "DESC"}disabled{else}onclick=&quot;sendReq('../{$module}/inc/ajax.php', 'action,sort,sort_order,sesid', '{$sort.action},{$h.sort},DESC,{$sesid}', do_element);&quot; title=&quot;{$label_list_sort_desc}&quot; alt=&quot;sort&quot;{/if}>
					<i class=&quot;fa fa-sort-desc list-icon&quot;></i>
				</button>
				<button type=&quot;button&quot; class=&quot;btn btn-default&quot; {if $sort.akt == $h.sort AND $sort.akt_order == "ASC"}disabled{else}onclick=&quot;sendReq('../{$module}/inc/ajax.php', 'action,sort,sort_order,sesid', '{$sort.action},{$h.sort},ASC,{$sesid}', do_element);&quot; title=&quot;{$label_list_sort_asc}&quot; alt=&quot;sort&quot;{/if}>
					<i class=&quot;fa fa-sort-asc list-icon&quot;></i>
				</button>
			</div>
			"{/if} data-column-name="{$h.db_name}">
				{if $sort.show && $sort.akt == $h.sort && $h.sort}
					<span class="glyphicon {if $sort.akt_order == "DESC"}glyphicon-chevron-down{else}glyphicon-chevron-up{/if}"></span>
				{/if}
				{$h.name}
			</th>
		{/foreach}
	</tr>
	</thead>

	<tbody>
	{foreach from=$tpl_list_data item=l}
		<tr onmousedown="{$l.rowclick_code}" class="{cycle values="row-even, row-odd"}{if $l.rowclass} {$l.rowclass}{/if}">
			<td nowrap class="list-action-header" style="width:50px;">
				<ul class="list-action-header">
			{foreach from=$show_control_cols key='k' item='control_col'}
				{if $k=='check'}
					<li class="list-check list-action-header">
						{if $l.show_checkbox}
							<input type="checkbox" class="nomargin" name="chk[{$l.id}]" id="chk[{$l.id}]" onclick="{$checkbox_code}" title="id: {$l.id}" />
						{elseif $l.show_numberfield}
							<input type="text" class="input-sm form-control" onclick="return false;" name="txt[{$l.id}]" size="{$l.numberfield_size}" maxlength="{$l.numberfield_size}" />
						{/if}
					</li>
				{elseif $k=='family'}
					<li class="list-family list-action-header">
						{if $l.show_fam_checkbox}
							<input type="checkbox" class="nomargin" name="famchk[{$l.famid}]" />
						{/if}
					</li>
				{elseif $k=='version'}
					<li class="list-version list-action-header">
						<button type="button" class="icon list-icon" alt="{$l.alt_version}" title="{$l.alt_version}" onclick="{$l.onclick_version}">
							<i class="fa fa-history"></i>
						</button>
					</li>
				{elseif $k=='crm'}
					<li class="list-crm list-action-header">
						<button type="button" class="icon list-icon leute-crm-btn" alt="{$l.alt_crm}" title="{$l.alt_crm}" data-id="{$l.id}">
							<i class="fa fa-comments"></i>
						</button>
					</li>
				{elseif $k=='maps'}
					<li class="list-maps list-action-header">
						{if $l.maps_link != ''}
							<a class="list-icon" href="{$l.maps_link}" target="_blank" alt="Google Maps" title="{$label_google_maps}">
								<i class="fa fa-map-marker"></i>
							</a>
						{/if}
					</li>
				{elseif $k=='whatsappclicktochat' && $l.mobilenumber}
					<li class="list-whatsappclicktochat list-action-header">
						<button type="button" class="icon list-icon">
							<a class="list-icon" href="https://wa.me/{$l.mobilenumber}" target="_blank" alt="WhatsApp Chat" title="{$label_whatsappclicktochat}">
								<i class="fa fa-whatsapp"></i>
							</a>
						</button>
					</li>
				{elseif $k=='clipboard'}
					<li class="list-clipboard list-action-header">
						<button type="button" class="icon list-icon">
							<span class="clipboardContainer" data-clipboard-text="{$l.clipboard_content}" title="{$label_clipboard}">
								<i class="fa fa-clipboard"></i>
							</span>
						</button>
					</li>
				{elseif $k=='template'}
					<li class="list-word list-action-header">
						<a href="#" class="list-icon" onclick="jumpToUrl('?action=leute_action&id=details_settings&sel_cols=alle&sel_auswahl=markierte&ids={$l.id}');" title="{ll key="export_details"}">
							<i class="fa fa-file-text-o"></i>
						</a>
					</li>
				{elseif $k=='undelete'}
					<li class="list-undelete list-action-header">
						{if $l.show_undelete_button}
							<button class="icon list-icon" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}">
								<i class="fa fa-refresh"></i>
							</button>
						{/if}
					</li>
				{elseif $k=='qrcode'}
					<li class="list-qrcode list-action-header">
						<a class="list-icon" href="javascript:ko_image_popup('{$ko_path}inc/qrcode.php?s={$l.qrcode_string}&h={$l.qrcode_hash}&size=7');" title="{$label_qrcode}">
							<i class="fa fa-qrcode"></i>
						</a>
					</li>
				{elseif $k=='edit'}
					<li class="list-edit list-action-header">
						{if $l.show_edit_button}
							<button class="icon list-icon" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}">
								<i class="fa fa-edit"></i>
							</button>
						{/if}
					</li>
				{elseif $k=='delete'}
					<li class="list-delete list-action-header">
						{if $l.show_delete_button}
							<button type="submit" class="icon list-icon" alt="{$l.alt_delete}" title="{$l.alt_delete}" onclick="{$l.onclick_delete}">
								<i class="fa fa-remove"></i>
							</button>
						{/if}
					</li>
				{elseif $k=='togglehidden'}
					<li class="list-togglehidden list-action-header">
						{if $l.show_togglehidden_button}
							<button type="submit" class="icon list-icon" title="{$l.alt_togglehidden}" onclick="{$l.onclick_togglehidden}">
								<span class="fa-stack"><i class="fa fa-eye fa-stack-1x"></i><i class="fa fa-{if $l.hidden}plus{else}minus{/if}-circle fa-stack-sm fa-stack-bottom-right"></i></span>
							</button>
						{/if}
					</li>
				{elseif $k=='donations'}
					<li class="list-donations list-action-header">
						{if $l.show_donations_button}
							<button type="submit" class="icon list-icon" title="{$l.alt_donations}" onclick="{$l.onclick_donations}">
								<i class="fa fa-money"></i>
							</button>
						{/if}
					</li>
				{/if}
			{/foreach}
				</ul>
			</td>


			{if $show_overlay}
				<td width="20" class="list-edit-overlay" onmouseover="$('#overlay_{$l.id}').show();">
					<div style="position: relative;">
						<button type="button" class="icon" disabled>
							<i class="fa fa-plus-square list-icon"></i>
						</button>
						<div class="list_overlay" id="overlay_{$l.id}">
							<ul>
								<li>
									<i class="fa fa-plus-square list-icon"></i>
								</li>
								{foreach from=$show_overlay_cols key='k' item='control_col'}
									{if $k=='version'}
										<li>
											<button type="button" class="icon list-icon" alt="{$l.alt_version}" title="{$l.alt_version}" onclick="{$l.onclick_version}">
												<i class="fa fa-history"></i>
											</button>
										</li>
									{elseif $k=='crm'}
										{if $l.show_crm_button}
											<li>
												<button type="button" class="icon list-icon leute-crm-btn" alt="{$l.alt_crm}" title="{$l.alt_crm}" data-id="{$l.id}">
													<i class="fa fa-comments"></i>
												</button>
											</li>
										{/if}
									{elseif $k=='maps'}
										{if $l.maps_link != ''}
											<li>
												<a href="{$l.maps_link}" class="list-icon" target="_blank" alt="Google Maps" title="{$label_google_maps}">
													<i class="fa fa-map-marker"></i>
												</a>
											</li>
										{/if}
									{elseif $k=='whatsappclicktochat' && $l.mobilenumber}
										<li>
											<a class="list-icon" href="https://wa.me/{$l.mobilenumber}" target="_blank" alt="WhatsApp Chat" title="{$label_whatsappclicktochat}">
												<i class="fa fa-whatsapp"></i>
											</a>
										</li>
									{elseif $k=='clipboard'}
										<li>
											<a href="#" class="list-icon clipboardContainer" data-clipboard-text="{$l.clipboard_content}" title="{ll key="leute_list_clipboard"}">
												<i class="fa fa-clipboard"></i>
											</a>
										</li>
									{elseif $k=='template'}
										<li>
											<a href="#" class="list-icon" onclick="jumpToUrl('?action=leute_action&id=details_settings&sel_cols=alle&sel_auswahl=markierte&ids={$l.id}');" title="{ll key="export_details"}">
												<i class="fa fa-file-text-o"></i>
											</a>
										</li>
									{elseif $k=='qrcode'}
										<li>
											<a class="list-icon" href="javascript:ko_image_popup('{$ko_path}inc/qrcode.php?s={$l.qrcode_string}&h={$l.qrcode_hash}&size=7');" title="{$label_qrcode}">
												<i class="fa fa-qrcode"></i>
											</a>
										</li>
									{elseif $k=='edit'}
										{if $l.show_edit_button}
											<li>
												<button class="icon list-icon" alt="{$l.alt_edit}" title="{$l.alt_edit}" onclick="{$l.onclick_edit}">
													<i class="fa fa-edit"></i>
												</button>
											</li>
										{/if}
									{elseif $k=='delete'}
										{if $l.show_delete_button}
											<li>
												<button type="submit" class="icon list-icon" alt="{$l.alt_delete}" title="{$l.alt_delete}" onclick="{$l.onclick_delete}">
													<i class="fa fa-remove"></i>
												</button>
											</li>
										{/if}
									{elseif $k=='togglehidden'}
										{if $l.show_togglehidden_button}
											<li>
												<button type="submit" class="icon list-icon" title="{$l.alt_togglehidden}" onclick="{$l.onclick_togglehidden}">
													<span class="fa-stack"><i class="fa fa-eye fa-stack-1x"></i><i class="fa fa-{if $l.hidden}plus{else}minus{/if}-circle fa-stack-sm fa-stack-bottom-right"></i></span>
												</button>
											</li>
										{/if}
									{elseif $k=='donations'}
										{if $l.show_donations_button}
											<li>
												<button type="submit" class="icon list-icon" title="{$l.alt_donations}" onclick="{$l.onclick_donations}">
													<i class="fa fa-money"></i>
												</button>
											</li>
										{/if}
									{/if}
								{/foreach}
							</ul>
						</div>
					</div>
				</td>
			{/if}

			{foreach from=$tpl_list_cols item=c}
				{assign var="t" value=$tpl_edit_columns.$c}
				<td {if $t == "telp" || $t == "telg" || $t == "natel" || $t == "fax"}style="white-space: nowrap;"{/if} id="{$db_table}|{$l.id}|{$db_cols.$c}">
					{$l.$c}
				</td>
			{/foreach}

		</tr>
		{if 'version'|in_array:$show_control_cols || 'version'|in_array:$show_overlay_cols}
			<tr style="display: none;" class="full-line-row" name="version_tr_{$l.id}" id="version_tr_{$l.id}">
				<td colspan="{$colspan_all}">
					<div name="version_{$l.id}" id="version_{$l.id}"></div>
				</td>
			</tr>
		{/if}
		{if 'crm'|in_array:$show_control_cols || 'crm'|in_array:$show_overlay_cols}
			<tr style="display: none;" class="full-line-row" name="crm_tr_{$l.id}" id="crm_tr_{$l.id}">
				<td colspan="{$colspan_all}" class="bg-warning">
					<div name="crm_{$l.id}" id="crm_{$l.id}"></div>
				</td>
			</tr>
		{/if}
	{/foreach}

	{if $tpl_show_editrow}
		<tr class="row-info no-hover">
			<th class="dragtable-drag-boundary" align="left">
				<ul class="list-action-header">
					<li style="text-align:left;" class="list-check">
						{if $list_check_disabled}
							&nbsp;
						{else}
							<button type="button" class="icon" alt="check" onclick="select_all_list_chk(this);{$checkbox_all_code};">
								<i class="fa fa-check-square-o list-icon"></i>
							</button>
						{/if}
					</li>
				</ul>
			</th>
			<th></th>
			{foreach from=$tpl_edit_columns item=c}
				<th class="ko_list_footer dragable_column" style="text-align:right;" title="{ll key='list_sortcolumn_title'}">
					{if $c != ""}
						<button type="submit" class="icon" alt="{$multiedit_list_title}" title="{$multiedit_list_title}" onclick="set_action('multiedit', this);set_hidden_value('id', '{$c}', this);">
							<i class="fa fa-edit list-icon"></i>
						</button>
					{else}
						&nbsp;
					{/if}
				</th>
			{/foreach}
		</tr>
	{/if}
	</tbody>
</table>

<script>
	if(window.innerWidth <= 680) {ldelim}
		Tablesaw.init();
	{rdelim}

	$(document).ready(function() {ldelim}
		$('table.ko_list').dragtable({ldelim}
			dataHeader: 'data-column-name',
			items: '.dragable_column',
			start: function() {ldelim}
				if($('div.fht-thead').css('position') !== "fixed") {ldelim}
					$('div.fht-tbody').css("padding-top", "27px");
				{rdelim}
				$('div.fht-thead').hide();
				{rdelim},
			stop: function() {ldelim}
				ko_list_destroy_fixed_header();
				var new_order = $('table.ko_list').dragtable('order');
				update_dragtable(new_order);
				ko_list_init_fixed_header();
				{rdelim}
			{rdelim});
		{rdelim});
</script>

<div class="ko_list_check_all">
	<button class="check_all btn btn-primary btn-sm" onclick="select_all_list_chk(this);return false;" title="{$label_list_check}" alt="{$label_list_check}"><i class="fa fa-check-square-o"></i> {$label_list_check}</button>
</div>



{if !$tpl_hide_header}
	<div class="pagestats pull-right">
		<div class="btn-toolbar">
			<div class="btn-group btn-group-sm page-stats-box" role="group">
				<button class="btn btn-default" disabled>{$tpl_stats}</button>
			</div>

			{if $show_page_select}
				<div class="input-group input-group-sm page-select-box">
					<div class="input-group-btn auto-width">
						<button class="btn btn-sm btn-default" disabled>{$show_page_select_label}</button>
					</div>
					<select class="input-sm" name="sel_list_page" size="0" onchange="sendReq('../{$module}/inc/ajax.php', 'action,set_start,sesid', 'setstart,'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
						{foreach from=$show_page_values item=v key=k}
							<option value="{$v}" {if $v == $show_page_selected}selected="selected"{/if}>{$show_page_output.$k}</option>
						{/foreach}
					</select>
				</div>
			{/if}

			<div class="btn-group btn-group-sm paging-box">
				<button class="btn btn-default" alt="{$label_list_back}" title="{$label_list_back}"{if $tpl_prevlink_link neq ""} onclick="{$tpl_prevlink_link};return false;"{else} disabled{/if}>
					<i class="fa fa-arrow-left"></i>
				</button>
				<button class="btn btn-default" alt="{$label_list_next}" title="{$label_list_next}"{if $tpl_nextlink_link neq ""} onclick="{$tpl_nextlink_link};return false;"{else} disabled{/if}>
					<i class="fa fa-arrow-right"></i>
				</button>
			</div>
		</div>
	</div>
	<i class="clearfix"></i>
{/if}


{if $show_list_footer}
	<ul class="list-group ko_list_footer_2">
		{foreach from=$list_footer item=footer}
			<li class="list-group-item">
				{$footer.label}&nbsp;{$footer.button}
			</li>
		{/foreach}
	</ul>
{/if}
