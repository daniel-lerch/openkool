<div id="ko_listh_filterbox"></div>


{if $list_warning}
	<div class="alert alert-warning alert-sm" role="alert">
		{$list_warning}
	</div>
{/if}

{if $list_title}
	<h3 class="ko_list_title">
		{if !$tpl_hide_header && $help.show}
			<span class="pull-left help-icon">&nbsp;{$help.link}</span>
		{/if}

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
			<span class="pull-left list-add-new">
				&nbsp;<a href="/{$module}/index.php?action={$action_new}" title="{ll key="list_title_plus"}"><i class="fa fa-plus"></i></a>
			</span>
		{/if}

		{if !$tpl_hide_header}
			<div class="pagestats pull-right">
				<div class="btn-toolbar">

					{if $show_colitemlist}
						<div class="btn-group btn-group-sm itemlist-box">
							<button type="button" class="btn btn-default" id="ko_list_colitemlist_click" data-toggle="popover" {if $show_flyout_header}data-title="{$label_flyout_header}" {/if}data-content="<div id=&quot;ko_list_colitemlist&quot;>{$colitemlist|replace:'"':'&quot;'}</div>"><i class="fa fa-list-ul"></i>&nbsp;{ll key="list_columns"}&nbsp;<i class="fa fa-caret-down"></i></button>
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

<table class="ko_list table table-bordered table-alternating table-hover {if $list.sortable}sortable{/if} tablesaw tablesaw-stack" data-tablesaw-mode="stack" data-table="{$list.table}" data-sort-col="{$list.sort.akt}" data-sort-order="{$list.sort.akt_order}">
	<thead>
	<tr class="row-info no-hover">
		<th class="ko_list list-check notdraggable dragtable-drag-boundary" style="width:auto;">
		{assign var="edit_cols_num" value=0}
		{foreach from=$edit_cols item=col}
			{if $col == "chk"}
				{assign var="edit_cols_num" value=$edit_cols_num+1}
					{if $list_check_disabled}
						&nbsp;
					{else}
						<button class="icon list-icon" onclick="select_all_list_chk(this);return false;" title="{$label_list_check}" alt="{$label_list_check}"><i class="fa fa-check-square-o"></i></button>
					{/if}
			{/if}
		{/foreach}
		</th>


		{assign var="nonFilterCounter" value=0}
		{foreach from=$list.header item=h}
			<th class="{if $h.class}{$h.class}{else}ko_list{/if} nowrap {if $h.filter || $h.sort}ko_listh_filter{/if} {if $h.filter_state == 'active'}ko_listh_filter_act{/if}"{if ($h.filter || ($list.sort.show && $h.sort != ""))} data-toggle="popover"{/if} id="listh_{if $h.filter}{$h.filter}{else}{$nonFilterCounter}{/if}"{if $h.filter} title="{$label.kota_filter}"{/if} data-table="{$list.table}" data-filter-enabled="{if $h.filter}true{else}false{/if}"{if $list.sort.show && $h.sort != ""} data-sort-enabled="true" data-sort-by="{$h.sort}" data-sort-action="{$list.sort.action}"{else} data-sort-enabled="false"{/if} data-column-name="{$h.db_name}">
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
			<td nowrap class="list-action-header" style="width:50px;">
				<ul class="list-action-header">
			{if $edit_cols.chk}
				<!-- Checkbox -->
				<li class="list-check list-action-header">
					<input type="checkbox" class="nomargin" name="chk[{$list.meta.$row.id}]" id="chk[{$list.meta.$row.id}]" title="id: {$list.meta.$row.id}" />
				</li>
			{/if}

			{if $edit_cols.numberfield}
				<li class="list-numberfield list-action-header">
					<input type="text" class="input-sm form-control" onclick="return false;" name="txt[{$list.meta.$row.id}]" size="{$list.meta.$row.numberfield_size}" maxlength="{$list.meta.$row.numberfield_size}">
				</li>
			{/if}

			{if $edit_cols.chk2}
				<!-- Familien-Checkbox -->
				{if $l.show_fam_checkbox}
					<li class="list-family list-action-header">
						<input type="checkbox" class="nomargin" name="famchk[{$l.famid}]" />
					</li>
				{else}
					<li class="list-family list-action-header list-hide-on-mobile">
					</li>
				{/if}
			{/if}

			{if $edit_cols.edit}
				{if $list.meta.$row.edit}
					<li class="list-edit list-action-header">
						<button class="icon list-icon" alt="{$label.alt_edit}" title="{$label.alt_edit}" onclick="set_action('{$list.actions.edit.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-edit"></i></button>
					</li>
				{else}
					<li class="list-edit list-action-header list-hide-on-mobile">
					</li>
				{/if}
			{/if}
			{if $edit_cols.check}
				{if $list.meta.$row.check}
					<li class="list-check list-action-header">
						<button class="icon list-icon" alt="{$label.alt_check}" title="{$label.alt_check}" onclick="{if $list.actions.check.confirm}c=confirm('{$label.confirm_check}');if(!c)return false;{/if}{$list.actions.check.additional_js}{$list.meta.$row.additional_row_js_check}set_action('{$list.actions.check.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-check"></i></button>
					</li>
				{else}
					<li class="list-check list-action-header list-hide-on-mobile">
					</li>
				{/if}
			{/if}
			{if $edit_cols.send}
				<li class="list-send list-action-header">
					<button class="icon list-icon" alt="{$label.alt_send}" title="{$label.alt_send}" onclick="set_action('{$list.actions.send.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-send"></i></button>
				</li>
			{/if}
			{if $edit_cols.undelete}
				<li class="list-undelete list-action-header">
					<button class="icon list-icon" alt="{$label.alt_undelete}" title="{$label.alt_undelete}" onclick=""><i class="fa fa-reload"></i></button>
				</li>
			{/if}
			{if $edit_cols.delete}
				{if $list.meta.$row.delete}
					<li class="list-delete list-action-header">
						<button class="icon list-icon" alt="{$label.alt_delete}" title="{$label.alt_delete}" onclick="{if $list.actions.delete.confirm}c=confirm('{$label.confirm_delete}'); if(!c) return false; {/if}{$list.actions.delete.additional_js}{$list.meta.$row.additional_row_js_delete}set_action('{$list.actions.delete.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-remove"></i></button>
					</li>
				{else}
					<li class="list-delete list-action-header list-hide-on-mobile">
					</li>
				{/if}
			{/if}
			{if $edit_cols.tracking_add || $edit_cols.tracking_show}
				{if $list.meta.$row.tracking_show}
					<li class="list-tracking list-action-header">
						<input type="image" src="{$ko_path}images/tracking_show.png" alt="{$label.alt_tracking_show}" title="{$label.alt_tracking_show}" onclick="jumpToUrl('{$ko_path}tracking/index.php?action=select_tracking&amp;id={$list.meta.$row.tracking_id}'); return false;" />
					</li>
				{elseif $list.meta.$row.tracking_add}
					<li class="list-tracking list-action-header">
						<input type="image" src="{$ko_path}images/tracking_add.png" alt="{$label.alt_tracking_add}" title="{$label.alt_tracking_add}" onclick="sendReq('{$ko_path}groups/inc/ajax.php', 'action,id,sesid', 'addgrouptracking,{$list.meta.$row.id},{$sesid}', do_element); return false;" />
					</li>
				{else}
					<li class="list-tracking list-action-header list-hide-on-mobile">
					</li>
				{/if}
			{/if}
			{if $edit_cols.mailing}
				<li class="list-mailing list-action-header{if $list.meta.$row.mailing_link|is_array} list-mailing-multiple popover-overlay-trigger{/if}" id="mailing-{$list.meta.$row.id}">
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
								content: '<table><tbody>{math assign="tableCols" equation='ceil(x/7)' x=$list.meta.$row.mailing_link|@sizeof}{assign var="loopCounter" value=0}{foreach from=$list.meta.$row.mailing_link item="mailitem"}{if $loopCounter == 0}<tr>{/if}<li><a href="mailto:{$mailitem.link}" title="{$mailitem.link}">{$mailitem.title}</a></li>{assign var="loopCounter" value=$loopCounter+1}{if $loopCounter >= $tableCols}</tr>{assign val="loopCounter" value=0}{/if}{/foreach}{if $loopCounter != 0}</tr>{/if}</tbody></table>',
								template: '<div class="popover popover-overlay popover-mailing" role="tooltip"><div class="arrow"></div><div class="popover-title"></div><div class="popover-content"></div></div>',
								trigger: 'manual'
								{rdelim});
						</script>
					{/if}
				</li>
			{/if}
			{if $edit_cols.add}
				{if $list.meta.$row.add}
					<li class="list-add list-action-header">
						<button class="icon list-icon" alt="{ll key="list_label_add_entry"}" title="{ll key="list_label_add_entry"}" onclick="set_action('{$list.actions.add.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-plus"></i></button>
					</li>
				{else}
					<li class="list-add list-action-header list-hide-on-mobile">
					</li>
				{/if}
			{/if}
			{if $edit_cols.remove}
				{if $list.meta.$row.remove}
					<li class="list-remove list-action-header">
						<button class="icon list-icon" alt="{ll key="list_label_remove_entry"}" title="{ll key="list_label_remove_entry"}" onclick="set_action('{$list.actions.remove.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-minus"></i></button>
					</li>
				{else}
					<li class="list-remove list-action-header list-hide-on-mobile">
					</li>
				{/if}
			{/if}
			{if $edit_cols.stats}
				{if $list.meta.$row.stats}
					<li class="list-stats list-action-header">
						<button class="icon list-icon" alt="{ll key="list_label_stats"}" title="{ll key="list_label_stats"}" onclick="set_action('{$list.actions.stats.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);"><i class="fa fa-pie-chart"></i></button>
					</li>
				{else}
					<li class="list-stats list-action-header list-hide-on-mobile">
					</li>
				{/if}
			{/if}

			{if $edit_cols.overlay}
				<li class="list-action-header list-edit-overlay"  onmouseover="$('#overlay_{$list.meta.$row.id}').show();" onmouseout="$('#overlay_{$list.meta.$row.id}').hide();">
					{if $list.meta.$row.overlay_icons|is_array && $list.meta.$row.overlay_icons|@sizeof > 0}

					<div style="position: relative;">
						<button type="button" class="icon" disabled>
							<i class="fa fa-plus-square list-icon"></i>
						</button>
						<div class="list_overlay" style="margin-left: 28px;" id="overlay_{$list.meta.$row.id}">

							<table><tbody>{math assign="tableCols" equation='ceil(x/7)' x=$list.meta.$row.overlay_icons|@sizeof}{assign var="loopCounter" value=0}{foreach from=$list.meta.$row.overlay_icons item="item"}{if $loopCounter == 0}<tr>{/if}<td><a href="{if $item.link !== NULL}{$item.link}{else}#{/if}"{if $item.title !== NULL} title="{$item.title}"{/if}{if $item.onclick !== NULL} onclick="{$item.onclick|replace:'"':'\"'}"{/if}{if $item.params !== NULL} {$item.params}{/if}>{$item.icon}&nbsp;&nbsp;{$item.text}</a></td>{assign var="loopCounter" value=$loopCounter+1}{if $loopCounter >= $tableCols}</tr>{assign var="loopCounter" value=0}{/if}{/foreach}{if $loopCounter != 0}</tr>{/if}</tbody></table>
						</div>
					</div>
					{/if}
				</li>
			{/if}

			</ul></td>

			{assign var="col_counter" value=0}
			{foreach from=$l item=c}
				<td {$list.meta.colparams.$col_counter} id="{$list.meta.id.$row.$col_counter}">
					{$c}
				</td>
				{assign var="col_counter" value=$col_counter+1}
			{/foreach}

		</tr>
	{/foreach}

		<tr class="row-info ko_list_footer no-hover">
			<th class="dragtable-drag-boundary">
				<ul class="list-action-header">
			<li style="text-align:left;" class="list-check">
				{if $list_check_disabled}
					&nbsp;
				{else}
					<button class="icon list-icon" border="0" width="13" height="13" title="{$label_list_check}" alt="{$label_list_check}" onclick="select_all_list_chk(this);return false;"><i class="fa fa-check-square-o"></i></button>
				{/if}
			</li>
			{foreach from=$edit_cols item=col}
				{if $col == "chk"}{/if}
				{if $col == "chk2"}
					<li class="ko_list ko_list_footer list-family"></li>
				{/if}
				{if $col == "edit" || $col == "check" || $col == "forward" || $col == "delete" || $col == "undelete" || $col == 'mailing' || $col == 'tracking_show' || $col == 'add' || $col == 'remove' || $col == 'send' || $col == 'overlay' || $col == 'stats'}
					<li class="ko_list ko_list_footer list-{$col}"></li>
				{/if}
			{/foreach}
			</ul>
		</th>
			{foreach from=$footer_cols item=c}
				<th class="ko_list_footer{if $disableManualSortingColumns === FALSE} dragable_column{/if}" style="text-align:right;" title="{ll key='list_sortcolumn_title'}">
					{if $c != ""}
						<button class="icon list-icon" alt="{$multiedit_list_title}" title="{$multiedit_list_title}" onclick="set_action('multiedit', this);set_hidden_value('id', '{$c}', this);"><i class="fa fa-edit"></i></button>
					{else}
						&nbsp;
					{/if}
				</th>
			{/foreach}
		</tr>
	</tbody>
</table>
<script>
	if(window.innerWidth <= 680) {ldelim}
		Tablesaw.init();
		{rdelim}

	{if $disableManualSortingColumns === FALSE}
	$(document).ready(function() {ldelim}
		$('table.ko_list').dragtable({ldelim}
			dataHeader: 'data-column-name',
			items: '.dragable_column',
			start: function() {ldelim}
				if($('div.fht-thead').css('position') !== "fixed") {ldelim}
					$('div.fht-tbody').css("padding-top", "35px");
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
	{/if}
</script>

<div class="ko_list_check_all">
	<button class="check_all btn btn-primary btn-sm" onclick="select_all_list_chk(this);return false;" title="{$label_list_check}" alt="{$label_list_check}"><i class="fa fa-check-square-o"></i> {$label_list_check}</button>
</div>

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
