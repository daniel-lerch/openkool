<div id="ko_listh_filterbox"></div>


{if $list_warning}
	<div class="alert alert-warning alert-sm" role="alert">
		{$list_warning}
	</div>
{/if}

{if $list_title}
	<h3 class="ko_list_title">
		<span class="pull-left">
			{$list_title}
			{if $list_subtitle}
				&nbsp;
				<small>
					{$list_subtitle}
				</small>
			{/if}
		</span>
		{if $action_new}
			<span class="pull-left">
				&nbsp;<a href="/{$module}/index.php?action={$action_new}" title="{ll key="list_title_plus"}"><i class="fa fa-plus"></i></a>
			</span>
		{/if}
		{if !$tpl_hide_header && $help.show}
			<span class="pull-left">&nbsp;{$help.link}</span>
		{/if}

		{if !$tpl_hide_header}
			<div class="pagestats pull-right">
				<div class="btn-toolbar">

					{if $show_colitemlist}
						<div class="btn-group btn-group-sm itemlist-box">
							<button type="button" class="btn btn-default" id="ko_list_colitemlist_click" data-toggle="popover" {if $show_flyout_header}data-title="{$label_flyout_header}" {/if}data-content="<div id=&quot;ko_list_colitemlist&quot;>{$colitemlist|replace:'"':'&quot;'}</div>"><i class="fa fa-list-ul"></i></button>
						</div>
						<script>
							$('#ko_list_colitemlist_click[data-toggle="popover"]').popover({ldelim}
								trigger: 'manual',
								html: 'true',
								container: 'body',
								placement: 'bottom'
							{rdelim});
						</script>
					{/if}

					<div class="btn-group btn-group-sm" role="group">
						<button class="btn btn-default" disabled>{$stats.start} - {$stats.end} {$stats.oftotal} {$stats.total|number_format:0:'.':"'"}</button>
					</div>

					{if !$stats.hide_listlimiticons}
						<div class="btn-group btn-group-sm limiting-box" role="group">
							<button type="button" class="btn btn-default" alt="-" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', '{if $stats.limitAction != ''}{$stats.limitAction}{else}setstart{/if},{$stats.limitM},{$sesid}', do_element);">
								<span class="glyphicon glyphicon-minus"></span>
							</button>
							<button type="button" class="btn btn-default" alt="+" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', '{if $stats.limitAction != ''}{$stats.limitAction}{else}setstart{/if},{$stats.limitP},{$sesid}', do_element);">
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
						<button class="btn btn-default" alt="{$label_list_back}" title="{$label_list_back}"{if $paging.prev neq ""} onclick="{$paging.prev};return false;"{else} disabled{/if}>
							<span class="glyphicon glyphicon-arrow-left"></span>
						</button>
						<button class="btn btn-default" alt="{$label_list_next}" title="{$label_list_next}"{if $paging.next neq ""} onclick="{$paging.next};return false;"{else} disabled{/if}>
							<span class="glyphicon glyphicon-arrow-right"></span>
						</button>
					</div>

				</div>
			</div>
			<br clear="all" />

		{/if}
	</h3>
{/if}

<table class="ko_list table table-condensed table-bordered table-alternating table-hover {if $list.sortable}sortable{/if}" data-table="{$list.table}" data-sort-col="{$list.sort.akt}" data-sort-order="{$list.sort.akt_order}">
	<thead>
	<tr class="row-info no-hover">
		{assign var="edit_cols_num" value=0}
		{foreach from=$edit_cols item=col}
			{if $col == "chk"}
				{assign var="edit_cols_num" value=$edit_cols_num+1}
				<th class="ko_list list-check">
					{if $list_check_disabled}
						&nbsp;
					{else}
						<button class="icon list-icon" onclick="select_all_list_chk();return false;" title="{$label_list_check}" alt="{$label_list_check}"><i class="fa fa-check-square-o"></i></button>
					{/if}
				</th>
			{/if}
			{if $col == "chk2"}
				{assign var="edit_cols_num" value=$edit_cols_num+1}
				<th class="ko_list list-family">
					<a href="#" title="{$label_list_check_family}" alt="{$label_list_check_family}" onclick="select_all_fam_chk();return false;"><i class="fa fa-users"></i></a>
				</th>
			{/if}
			{if $col == "edit" || $col == "check" || $col == "forward" || $col == "delete" || $col == "undelete" || $col == 'mailing' || $col == 'tracking_show' || $col == 'add' || $col == 'remove' || $col == 'send' || $col == 'overlay' || $col == 'stats'}
				{assign var="edit_cols_num" value=$edit_cols_num+1}
				<th class="ko_list list-{$col}"></th>
			{/if}
		{/foreach}



		{assign var="nonFilterCounter" value=0}
		{foreach from=$list.header item=h}
			<th class="{if $h.class}{$h.class}{else}ko_list{/if} nowrap {if $h.filter || $h.sort}ko_listh_filter{/if} {if $h.filter_state == 'active'}ko_listh_filter_act{/if}"{if ($h.filter || ($list.sort.show && $h.sort != ""))} data-toggle="popover"{/if} id="listh_{if $h.filter}{$h.filter}{else}{$nonFilterCounter}{/if}"{if $h.filter} title="{$label.kota_filter}"{/if} data-table="{$list.table}" data-filter-enabled="{if $h.filter}true{else}false{/if}"{if $list.sort.show && $h.sort != ""} data-sort-enabled="true" data-sort-by="{$h.sort}" data-sort-action="{$list.sort.action}"{else}data-sort-enabled="false"{/if}>
				{if $list.sort.akt == $h.sort && $h.sort}
					<span class="glyphicon {if $list.sort.akt_order == "DESC"}glyphicon-chevron-down{else}glyphicon-chevron-up{/if}"></span>
				{/if}
				{$h.name}
			</th>
			{assign var="nonFilterCounter" value=$nonFilterCounter+1}
		{/foreach}
	</tr>
	</thead>

	<tbody>
	{foreach from=$list.data item=l key=row}
		<tr class="{cycle values="row-even, row-odd"} {$list.meta.$row.rowclass} row-striped" onmousedown="{$l.rowclick_code}" data-id="{$list.meta.$row.id}">

			{if $edit_cols.chk}
				<!-- Checkbox -->
				<td class="list-check list-action-header">
					<input type="checkbox" class="nomargin" name="chk[{$list.meta.$row.id}]" id="chk[{$list.meta.$row.id}]" title="id: {$list.meta.$row.id}" />
				</td>
			{/if}

			{if $edit_cols.numberfield}
				<td class="list-numberfield list-action-header">
					<input type="text" class="input-sm form-control" onclick="return false;" name="txt[{$list.meta.$row.id}]" size="{$list.meta.$row.numberfield_size}" maxlength="{$list.meta.$row.numberfield_size}">
				</td>
			{/if}

			{if $edit_cols.chk2}
				<!-- Familien-Checkbox -->
				<td class="list-family list-action-header">
					{if $l.show_fam_checkbox}
						<input type="checkbox" class="nomargin" name="famchk[{$l.famid}]" />
					{else}
						&nbsp;
					{/if}
				</td>
			{/if}

			{if $edit_cols.edit}
				<td class="list-edit list-action-header">
					{if $list.meta.$row.edit}
						<button class="icon list-icon" alt="{$label.alt_edit}" title="{$label.alt_edit}" onclick="javascript:set_action('{$list.actions.edit.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-edit"></i></button>
					{else}
						&nbsp;
					{/if}
				</td>
			{/if}
			{if $edit_cols.check}
				<td class="list-check list-action-header">
					{if $list.meta.$row.check}
						<button class="icon list-icon" alt="{$label.alt_check}" title="{$label.alt_check}" onclick="javascript:{if $list.actions.check.confirm}c=confirm('{$label.confirm_check}');if(!c)return false;{/if}{$list.actions.check.additional_js}{$list.meta.$row.additional_row_js_check}set_action('{$list.actions.check.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-check"></i></button>
					{/if}
				</td>
			{/if}
			{if $edit_cols.send}
				<td class="list-send list-action-header">
					<button class="icon list-icon" alt="{$label.alt_send}" title="{$label.alt_send}" onclick="javascript:set_action('{$list.actions.send.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-send"></i></button>
				</td>
			{/if}
			{if $edit_cols.undelete}
				<td class="list-undelete list-action-header">
					<button class="icon list-icon" alt="{$label.alt_undelete}" title="{$label.alt_undelete}" onclick=""><i class="fa fa-reload"></i></button>
				</td>
			{/if}
			{if $edit_cols.delete}
				<td class="list-delete list-action-header">
					{if $list.meta.$row.delete}
						<button class="icon list-icon" alt="{$label.alt_delete}" title="{$label.alt_delete}" onclick="javascript:{if $list.actions.delete.confirm}c=confirm('{$label.confirm_delete}'); if(!c) return false; {/if}{$list.actions.delete.additional_js}{$list.meta.$row.additional_row_js_delete}set_action('{$list.actions.delete.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-remove"></i></button>
					{else}
						&nbsp;
					{/if}
				</td>
			{/if}
			{if $edit_cols.tracking_add || $edit_cols.tracking_show}
				<td class="list-tracking list-action-header">
					{if $list.meta.$row.tracking_show}
						<input type="image" src="{$ko_path}images/tracking_show.png" alt="{$label.alt_tracking_show}" title="{$label.alt_tracking_show}" onclick="javascript:jumpToUrl('{$ko_path}tracking/index.php?action=enter_tracking&amp;id={$list.meta.$row.tracking_id}'); return false;" />
					{elseif $list.meta.$row.tracking_add}
						<input type="image" src="{$ko_path}images/tracking_add.png" alt="{$label.alt_tracking_add}" title="{$label.alt_tracking_add}" onclick="javascript:sendReq('{$ko_path}groups/inc/ajax.php', 'action,id,sesid', 'addgrouptracking,{$list.meta.$row.id},{$sesid}', do_element); return false;" />
					{/if}
				</td>
			{/if}
			{if $edit_cols.mailing}
				<td class="list-mailing list-action-header{if $list.meta.$row.mailing_link|is_array} list-mailing-multiple popover-overlay-trigger{/if}" id="mailing-{$list.meta.$row.id}">
					<div style="position:relative">
						{if $list.meta.$row.mailing}
							<a class="list-icon" href="{if $list.meta.$row.mailing_link|is_array}#{else}mailto:{$list.meta.$row.mailing_link}{/if}" alt="@" title="{$label.alt_mailing}"><i class="fa fa-send"></i></a>
						{/if}
					</div>
					{if $list.meta.$row.mailing_link|is_array}
						<script>
							$('#mailing-{$list.meta.$row.id}').popover({ldelim}
								html: true,
								container: 'body',
								title: '{ll key="form_groups_mailing_address"}',
								content: '<table><tbody>{math assign="tableCols" equation='ceil(x/7)' x=$list.meta.$row.mailing_link|@sizeof}{assign var="loopCounter" value=0}{foreach from=$list.meta.$row.mailing_link item="mailitem"}{if $loopCounter == 0}<tr>{/if}<td><a href="mailto:{$mailitem.link}" title="{$mailitem.link}">{$mailitem.title}</a></td>{assign var="loopCounter" value=$loopCounter+1}{if $loopCounter >= $tableCols}</tr>{assign val="loopCounter" value=0}{/if}{/foreach}{if $loopCounter != 0}</tr>{/if}</tbody></table>',
								template: '<div class="popover popover-overlay popover-mailing" role="tooltip"><div class="arrow"></div><div class="popover-title"></div><div class="popover-content"></div></div>',
								trigger: 'manual'
							{rdelim});
						</script>
					{/if}
				</td>
			{/if}
			{if $edit_cols.add}
				<td class="list-add list-action-header">
					{if $list.meta.$row.add}
						<button class="icon list-icon" alt="{ll key="list_label_add_entry"}" title="{ll key="list_label_add_entry"}" onclick="javascript:set_action('{$list.actions.add.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-plus"></i></button>
					{/if}
				</td>
			{/if}
			{if $edit_cols.remove}
				<td class="list-remove list-action-header">
					{if $list.meta.$row.remove}
						<button class="icon list-icon" alt="{ll key="list_label_remove_entry"}" title="{ll key="list_label_remove_entry"}" onclick="javascript:set_action('{$list.actions.remove.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-minus"></i></button>
					{/if}
				</td>
			{/if}
			{if $edit_cols.stats}
				<td class="list-stats list-action-header">
					{if $list.meta.$row.stats}
						<button class="icon list-icon" alt="{ll key="list_label_stats"}" title="{ll key="list_label_stats"}" onclick="javascript:set_action('{$list.actions.stats.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-pie-chart"></i></button>
					{/if}
				</td>
			{/if}
			{if $edit_cols.overlay}
				<td class="list-overlay list-action-header popover-overlay-trigger" id="overlay-{$list.meta.$row.id}">
					{if $list.meta.$row.overlay_icons|is_array && $list.meta.$row.overlay_icons|@sizeof > 0}
						<a class="list-icon" href="#""><i class="fa fa-plus-square"></i></a>
						<script>
							$('#overlay-{$list.meta.$row.id}').popover({ldelim}
								html: true,
								container: 'body',
								content: '<table><tbody>{math assign="tableCols" equation='ceil(x/7)' x=$list.meta.$row.overlay_icons|@sizeof}{assign var="loopCounter" value=0}{foreach from=$list.meta.$row.overlay_icons item="item"}{if $loopCounter == 0}<tr>{/if}<td><a href="{if $item.link !== NULL}{$item.link}{else}#{/if}"{if $item.title !== NULL} title="{$item.title}"{/if}{if $item.onclick !== NULL} onclick="{$item.onclick}"{/if}{if $item.params !== NULL} {$item.params}{/if}>{$item.icon}&nbsp;&nbsp;{$item.text}</a></td>{assign var="loopCounter" value=$loopCounter+1}{if $loopCounter >= $tableCols}</tr>{assign val="loopCounter" value=0}{/if}{/foreach}{if $loopCounter != 0}</tr>{/if}</tbody></table>',
								template: '<div class="popover popover-overlay popover-actions" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>',
								trigger: 'manual'
							{rdelim});
						</script>
					{/if}
				</td>
			{/if}

			{assign var="col_counter" value=0}
			{foreach from=$l item=c}
				<td {$list.meta.colparams.$col_counter} id="{$list.meta.id.$row.$col_counter}">
					{$c}
				</td>
				{assign var="col_counter" value=$col_counter+1}
			{/foreach}

		</tr>
	{/foreach}

	{if $show_multiedit}
		<tr class="row-info ko_list_footer no-hover">
			<th style="text-align:left;" class="list-check">
				{if $list_check_disabled}
					&nbsp;
				{else}
					<button class="icon list-icon" border="0" width="13" height="13" title="{$label_list_check}" alt="{$label_list_check}" onclick="select_all_list_chk();return false;"><i class="fa fa-check-square-o"></i></button>
				{/if}
			</th>
			{foreach from=$edit_cols item=col}
				{if $col == "chk"}{/if}
				{if $col == "chk2"}
					<th class="ko_list ko_list_footer list-family"></th>
				{/if}
				{if $col == "edit" || $col == "check" || $col == "forward" || $col == "delete" || $col == "undelete" || $col == 'mailing' || $col == 'tracking_show' || $col == 'add' || $col == 'remove' || $col == 'send' || $col == 'overlay' || $col == 'stats'}
					<th class="ko_list ko_list_footer list-{$col}"></th>
				{/if}
			{/foreach}
			{foreach from=$multiedit_cols item=c}
				<th class="ko_list_footer" style="text-align:right;">
					{if $c != ""}
						<button class="icon list-icon" alt="{$multiedit_list_title}" title="{$multiedit_list_title}" onclick="set_action('multiedit', this);set_hidden_value('id', '{$c}', this);"><i class="fa fa-edit"></i></button>
					{else}
						&nbsp;
					{/if}
				</th>
			{/foreach}
		</tr>
	{/if}


	{if $tpl_show_sort_cols}
		<tr class="ko_list_footer">
			{if $tpl_show_3cols}
			<th class="ko_list_footer" colspan="3" align="left">
				{elseif $tpl_show_4cols_leute}
			<th class="ko_list_footer" colspan="4" align="left">
				{else}
			<th class="ko_list_footer" align="left">
				{/if}
				&nbsp;
			</th>
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
	</tbody>
</table>


{if !$tpl_hide_header}
	<div class="pagestats pull-right">
		<div class="btn-toolbar">
			<div class="btn-group btn-group-sm" role="group">
				<button class="btn btn-default" disabled>{$stats.start} - {$stats.end} {$stats.oftotal} {$stats.total|number_format:0:'.':"'"}</button>
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
				<button class="btn btn-default" alt="{$label_list_back}" title="{$label_list_back}"{if $paging.prev neq ""} onclick="{$paging.prev};return false;"{else} disabled{/if}>
					<span class="glyphicon glyphicon-arrow-left"></span>
				</button>
				<button class="btn btn-default" alt="{$label_list_next}" title="{$label_list_next}"{if $paging.next neq ""} onclick="{$paging.next};return false;"{else} disabled{/if}>
					<span class="glyphicon glyphicon-arrow-right"></span>
				</button>
			</div>
		</div>
	</div>
	<i class="clearfix"></i>
{/if}


{if $list.footer.show}
	<ul class="list-group ko_list_footer_2">
		{foreach from=$list.footer.data item=footer}
			<li class="list-group-item">
				{$footer.label}&nbsp;
				{$footer.button}
			</li>
		{/foreach}
	</ul>
{/if}

{if $list.sortable}
	<script>
		{literal}$("table.ko_list.sortable tbody").sortable({items: "> tr.row-even, >tr.row-odd"});{/literal}
	</script>
{/if}

<script>
	ko_list_set_mobile_height();
</script>
