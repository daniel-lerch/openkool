<div id="ko_listh_filterbox"></div>

{if $list_warning}
    <div class="warntxt">{$list_warning}</div>
{/if}

{if $list_title}
    <div class="list-title">{$list_title}</div>
{/if}

{if $list_subtitle}
    <div class="list-subtitle">{$list_subtitle}</div>
{/if}

{if !$tpl_hide_header}
    {if $help.show}
        <div class="list-help">
            {$help.link}
        </div>
    {/if}

    <div align="right" class="pagestats">
        {if $show_colitemlist}
            <div id="ko_list_colitemlist">
                <div id="ko_list_colitemlist_click"></div>
                <div id="ko_list_colitemlist_flyout">
                    {$colitemlist}
                </div>
            </div>
            &nbsp;&nbsp;&nbsp;
        {/if}

        {$stats.start} - {$stats.end} {$stats.oftotal} {$stats.total|number_format:0:'.':"'"}
        {if !$stats.hide_listlimiticons}
            <img src="{$ko_path}/images/decrease.png" border="0" alt="-" style="margin-bottom: -3px; cursor: pointer;" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', '{if $stats.limitAction != ''}{$stats.limitAction}{else}setstart{/if},{$stats.limitM},{$sesid}', do_element);" />
            <img src="{$ko_path}/images/increase.png" border="0" alt="+" style="margin-bottom: -3px; cursor: pointer;" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', '{if $stats.limitAction != ''}{$stats.limitAction}{else}setstart{/if},{$stats.limitP},{$sesid}', do_element);" />
        {/if}
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


        {if $paging.prev neq ""}
            <a href="{$paging.prev}">
                <img src="{$ko_path}images/icon_arrow_left.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
            </a>
        {else}
            <img src="{$ko_path}images/icon_arrow_left_disabled.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
        {/if}

        {if $paging.next neq ""}
            <a href="{$paging.next}">
                <img src="{$ko_path}images/icon_arrow_right.png" border="0" alt="{$label_list_next}" title="{$label_list_next}" />
            </a>
        {else}
            <img src="{$ko_path}images/icon_arrow_right_disabled.png" border="0" alt="{$label_list_next}" title="{$label_list_next}" />
        {/if}
        &nbsp;

    </div>
    <br clear="all" />
{/if}

<table width="100%" class="ko_list {if $list.sortable}sortable{/if}" data-table="{$list.table}" cellpadding="0" cellspacing="0">
<tr>
    {assign var="edit_cols_num" value=0}
    {foreach from=$edit_cols item=col}
        {if $col == "chk"}
            {assign var="edit_cols_num" value=$edit_cols_num+1}
            <th class="ko_list list-check">
                {if $list_check_disabled}
                    &nbsp;
                {else}
                    <img src="{$ko_path}images/icon_checked.gif" border="0" width="13" height="13" title="{$label_list_check}" alt="{$label_list_check}" onclick="select_all_list_chk();" />
                {/if}
            </th>
        {/if}
        {if $col == "chk2"}
            {assign var="edit_cols_num" value=$edit_cols_num+1}
            <th class="ko_list list-family">
                <img src="{$ko_path}images/icon_familie.png" border="0" width="16" height="16" title="{$label_list_check_family}" alt="{$label_list_check_family}" onclick="select_all_fam_chk();" />
            </th>
        {/if}
        {if $col == "edit" || $col == "check" || $col == "forward" || $col == "delete" || $col == "undelete" || $col == 'mailing' || $col == 'tracking_show' || $col == 'add' || $col == 'remove' || $col == 'send'}
            {assign var="edit_cols_num" value=$edit_cols_num+1}
            <th class="ko_list list-{$col}"></th>
        {/if}
    {/foreach}



    {foreach from=$list.header item=h}
        <th class="{if $h.class}{$h.class}{else}ko_list{/if} {if $h.filter}ko_listh_filter{/if} {if $h.filter_state == 'active'}ko_listh_filter_act{/if}" id="listh_{$h.filter}" {if $h.filter}title="{$label.kota_filter}"{/if}>
	<span style="white-space:nowrap">
	{if $list.sort.show && $h.sort != ""}
        <span class="list-sorticon">
		{assign var="type" value="disabled"}
            {if $list.sort.akt == $h.sort AND $list.sort.akt_order == "DESC"} {assign var="type" value="enabled"} {/if}
            <a href="javascript:sendReq('../{$module}/inc/ajax.php', 'action,sort,sort_order,sesid', '{$list.sort.action},{$h.sort},DESC,{$sesid}', do_element);">
                <img src="{$ko_path}images/icon_arrow_down_{$type}.gif" alt="sort" border="0" title="{$label_list_sort_desc}" />
            </a>
		</span>
    {/if}

        {$h.name}

        {if $list.sort.show && $h.sort != ""}
            <span class="list-sorticon">
		{assign var="type" value="disabled"}
                {if $list.sort.akt == $h.sort AND $list.sort.akt_order == "ASC"} {assign var="type" value="enabled"} {/if}
                <a href="javascript:sendReq('../{$module}/inc/ajax.php', 'action,sort,sort_order,sesid', '{$list.sort.action},{$h.sort},ASC,{$sesid}', do_element);">
                    <img src="{$ko_path}images/icon_arrow_up_{$type}.gif" alt="sort" border="0" title="{$label_list_sort_asc}" />
                </a>
		</span>
        {/if}
	</span>
        </th>
    {/foreach}
</tr>

{foreach from=$list.data item=l key=row}
    <tr class="{cycle values="ko_list_even, ko_list_odd"} {$list.meta.$row.rowclass}" onmousedown="{$l.rowclick_code}" data-id="{$list.meta.$row.id}">

        {if $edit_cols.chk}
            <!-- Checkbox -->
            <td width="20" class="list-check">
                <input type="checkbox" name="chk[{$list.meta.$row.id}]" id="chk[{$list.meta.$row.id}]" title="id: {$list.meta.$row.id}" />
            </td>
        {/if}

        {if $edit_cols.numberfield}
            <td width="20" class="list-numberfield">
                <input type="text" onclick="return false;" name="txt[{$list.meta.$row.id}]" size="{$list.meta.$row.numberfield_size}" maxlength="{$list.meta.$row.numberfield_size}" />
            </td>
        {/if}

        {if $edit_cols.chk2}
            <!-- Familien-Checkbox -->
            <td width="20" class="list-family">
                {if $l.show_fam_checkbox}
                    <input type="checkbox" name="famchk[{$l.famid}]" />
                {else}
                    &nbsp;
                {/if}
            </td>
        {/if}

        {if $edit_cols.edit}
            <td width="20" class="list-edit">
                {if $list.meta.$row.edit}
                    <input type="image" src="{$ko_path}images/button_edit.gif" alt="{$label.alt_edit}" title="{$label.alt_edit}" onclick="javascript:set_action('{$list.actions.edit.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);" />
                {else}
                    &nbsp;
                {/if}
            </td>
        {/if}
        {if $edit_cols.check}
            <td width="20" class="list-check">
                {if $list.meta.$row.check}
                    <input type="image" src="{$ko_path}images/button_check.png" alt="{$label.alt_check}" title="{$label.alt_check}" onclick="javascript:{$list.actions.check.additional_js}{$list.meta.$row.additional_row_js_check}set_action('{$list.actions.check.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);" />
                {/if}
            </td>
        {/if}
        {if $edit_cols.send}
            <td width="20" class="list-send">
                <input type="image" src="{$ko_path}images/forward.gif" alt="{$label.alt_send}" title="{$label.alt_send}" onclick="javascript:set_action('{$list.actions.send.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);" />
            </td>
        {/if}
        {if $edit_cols.undelete}
            <td width="20" class="list-undelete">
                <input type="image" src="{$ko_path}images/undelete.png" alt="{$label.alt_undelete}" title="{$label.alt_undelete}" onclick="" />
            </td>
        {/if}
        {if $edit_cols.delete}
            <td width="20" class="list-delete">
                {if $list.meta.$row.delete}
                    <input type="image" src="{$ko_path}images/button_delete.gif" alt="{$label.alt_delete}" title="{$label.alt_delete}" onclick="javascript:{if $list.actions.delete.confirm}c=confirm('{$label.confirm_delete}'); if(!c) return false; {/if}{$list.actions.delete.additional_js}{$list.meta.$row.additional_row_js_delete}set_action('{$list.actions.delete.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);" />
                {else}
                    &nbsp;
                {/if}
            </td>
        {/if}
        {if $edit_cols.tracking_add || $edit_cols.tracking_show}
            <td width="20" class="list-tracking">
                {if $list.meta.$row.tracking_show}
                    <input type="image" src="{$ko_path}images/tracking_show.png" alt="{$label.alt_tracking_show}" title="{$label.alt_tracking_show}" onclick="javascript:jumpToUrl('{$ko_path}tracking/index.php?action=enter_tracking&amp;id={$list.meta.$row.tracking_id}'); return false;" />
                {elseif $list.meta.$row.tracking_add}
                    <input type="image" src="{$ko_path}images/tracking_add.png" alt="{$label.alt_tracking_add}" title="{$label.alt_tracking_add}" onclick="javascript:sendReq('{$ko_path}groups/inc/ajax.php', 'action,id,sesid', 'addgrouptracking,{$list.meta.$row.id},{$sesid}', do_element); return false;" />
                {/if}
            </td>
        {/if}
        {if $edit_cols.mailing}
            <td width="20" class="list-mailing">
                {if $list.meta.$row.mailing}
                    <a href="mailto:{$list.meta.$row.mailing_link}"><img src="{$ko_path}images/send_email.png" alt="@" title="{$label.alt_mailing}" border="0" /></a>
                {/if}
            </td>
        {/if}
        {if $edit_cols.add}
            <td width="20" class="list-mailing">
                {if $list.meta.$row.add}
                    <input type="image" src="{$ko_path}images/icon_plus.png" alt="{ll key="list_label_add_entry"}" title="{ll key="list_label_add_entry"}" onclick="javascript:set_action('{$list.actions.add.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);" />
                {/if}
            </td>
        {/if}
        {if $edit_cols.remove}
            <td width="20" class="list-mailing">
                {if $list.meta.$row.remove}
                    <input type="image" src="{$ko_path}images/icon_minus.png" alt="{ll key="list_label_remove_entry"}" title="{ll key="list_label_remove_entry"}" onclick="javascript:set_action('{$list.actions.remove.action}', this); set_hidden_value('id', '{$list.meta.$row.id}', this);" />
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
    <tr class="ko_list_footer">
        <th class="ko_list_footer" colspan="{$edit_cols_num}" align="left">
            {if $list_check_disabled}
                &nbsp;
            {else}
                <img src="{$ko_path}images/icon_checked.gif" border="0" width="13" height="13" title="{$label_list_check}" alt="{$label_list_check}" onclick="select_all_list_chk();" />
            {/if}
        </th>
        {foreach from=$multiedit_cols item=c}
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
</table>


{if !$tpl_hide_header}
    <div align="right" class="pagestats">
        {$stats.start} - {$stats.end} {$stats.oftotal} {$stats.total|number_format:0:'.':"'"}
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


        {if $paging.prev neq ""}
            <a href="{$paging.prev}">
                <img src="{$ko_path}images/icon_arrow_left.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
            </a>
        {else}
            <img src="{$ko_path}images/icon_arrow_left_disabled.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
        {/if}

        {if $paging.next neq ""}
            <a href="{$paging.next}">
                <img src="{$ko_path}images/icon_arrow_right.png" border="0" alt="{$label_list_next}" title="{$label_list_next}" />
            </a>
        {else}
            <img src="{$ko_path}images/icon_arrow_right_disabled.png" border="0" alt="{$label_list_next}" title="{$label_list_next}" />
        {/if}
        &nbsp;

    </div>
{/if}


{if $list.footer.show}
    <table style="margin-left:12px" cellspacing="0" cellpadding="3" class="list-footer">
        <tr><td style="border-left-style:solid;border-left-width:1px">&nbsp;</td></tr>

        {foreach from=$list.footer.data item=footer}
            <tr><td style="border-left-style:solid;border-left-width:1px;border-bottom-width:1px;border-bottom-style:solid;">
                    &nbsp;{$footer.label}
                    &nbsp;&nbsp;{$footer.button}
                </td></tr>
        {/foreach}

    </table>
{/if}
<br /><br />

{if $list.sortable}
    <script>
        {literal}$("table.ko_list.sortable tbody").sortable({items: "> tr.ko_list_even, >tr.ko_list_odd"});{/literal}
    </script>
{/if}
