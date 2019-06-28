{if !$hide_table_html}<tr><td colspan="3" class="submenu">{/if}
{if $show_flyout_header}
<div class="flyout_header">
	<b>{$label_flyout_header}</b>
</div>
{/if}

<strong><big>&middot;</big></strong>{$itemlist_open_preset}<br />
<select name="sel_itemlist{$action_suffix}" size="0" onchange="sendReq('../{$sm.mod}/inc/ajax.php', 'action,name,pos,sesid', 'itemlistopen{$action_suffix},'+this.options[this.selectedIndex].value+',{$sm.pos},{$sm.sesid}', do_element);{$itemlist_open_post}" style="width: 150px;">
<option value=""></option>
<option value="_all_">{$itemlist_preset_all}</option>
<option value="_none_">{$itemlist_preset_none}</option>
<option value="" disabled="disabled">----------</option>
{foreach from=$tpl_itemlist_values item=v key=k}
	<option value="{$v}" {if $v == $tpl_itemlist_selected}selected="selected"{/if}>{$tpl_itemlist_output.$k}</option>
{/foreach}
</select>
{if !$ko_guest}
	<input type="image" src="{$ko_path}images/icon_trash.png" alt="{$itemlist_delete_preset}" title="{$itemlist_delete_preset}" onclick="c = confirm('{$itemlist_delete_preset_confirm}');if(!c) return false; sendReq('../{$sm.mod}/inc/ajax.php', 'action,name,pos,sesid', 'itemlistdelete{$action_suffix},'+document.getElementsByName('sel_itemlist{$action_suffix}')[0].options[document.getElementsByName('sel_itemlist{$action_suffix}')[0].selectedIndex].value+',{$sm.pos},{$sm.sesid}', do_element); return false;" />
	<br /><br />
{/if}

<div class="itemlist">
{foreach from=$tpl_itemlist_select item=s}
	{assign var="close_div" value=""}

	<div class="itemlist_item">

		{if $s.name|truncate:3:"" == "---"}

			<b>{$s.name}</b>

		{elseif $s.type == "group"}

			<input type="checkbox" name="chk_itemlist_group_{$s.value}" id="chk_itemlist_group_{$action_suffix}_{$s.value}" value="{$s.value}" title="{$s.name|strip_tags}" {$s.params} {if $s.aktiv} checked="checked"{/if} onclick="sendReq('../{$sm.mod}/inc/ajax.php', 'action,id,state,sesid', 'itemlistgroup,{$s.value},'+this.checked+',{$sm.sesid}', do_element);{if $s.onclick_post != ''}{$s.onclick_post}{else}{$item_onclick_post}{/if}" />
			<a href="#" onclick="sendReq('../{$sm.mod}/inc/ajax.php', 'action,id,state,sesid', 'itemlisttogglegroup,{$s.value},{$s.open},{$sm.sesid}', do_element);change_vis('itemlist_group_{$action_suffix}_{$s.value}');s=document.getElementById('span_itemlist_group_{$action_suffix}_{$s.value}');{literal}if(s.className=='itemlist_group_0'){s.className='itemlist_group_1'}else{s.className='itemlist_group_0'}{/literal};">
			<span class="itemlist_group_{$s.open}" id="span_itemlist_group_{$action_suffix}_{$s.value}">{$s.name}{$closed}</span></a>

		{elseif $s.type == 'html'}

			{$s.html}

		{else}

			{if $s.parent}&nbsp;{/if}
			<input type="checkbox" name="itemlist_{$s.value}" id="itemlist_{$action_suffix}_{$s.value}" value="{$s.value}" title="{$s.name}" {$s.params} {if $s.aktiv} checked="checked"{/if}
			onclick="sendReq('../{$sm.mod}/inc/ajax.php', 'action,id,state,sesid', '{if $s.action != ''}{$s.action}{else}itemlist{/if},{$s.value},'+this.checked+',{$sm.sesid}', do_element);{if $s.onclick_post != ''}{$s.onclick_post}{else}{$item_onclick_post}{/if}" />
			{$s.prename}<label for="itemlist_{$action_suffix}_{$s.value}">{$s.name}</label>
			{if $s.last}{assign var="close_div" value="</div>"}{/if}

		{/if}

	</div>

	{if $s.type == "group"}
		<div id="itemlist_group_{$action_suffix}_{$s.value}" {if $s.open == 0}style="display: none; visibility: hidden;"{/if}>
	{/if}
	{$close_div}

{/foreach}
</div>
{if $show_sort_cols}
	<input type="checkbox" name="chk_sort_cols" id="chk_sort_cols" value="1" {$sort_cols_checked} onclick="sendReq('../{$sm.mod}/inc/ajax.php', 'action,state,sesid', 'itemlistsort,'+this.checked+',{$sm.sesid}', do_element);" /><label for="chk_sort_cols">{$itemlist_sortcols}</label><br />
{/if}
<br />

{if !$ko_guest}
	<strong><big>&middot;</big></strong>{$itemlist_save_preset}<br />
	{if $allow_global} <div style="font-size:9px;"><input type="checkbox" name="chk_itemlist_global{$action_suffix}" value="1" />{$itemlist_global}</div> {/if}
	<input type="text" name="txt_itemlist_new{$action_suffix}" style="width:135px;" />
	<input type="image" src="{$ko_path}images/icon_save.gif" id="save_itemlist_{$action_suffix}" alt="{$itemlist_save_preset}" title="{$itemlist_save_preset}" onclick="sendReq('../{$sm.mod}/inc/ajax.php', 'action,name{if $allow_global},global{/if},pos,sesid', 'itemlistsave{$action_suffix},'+document.getElementsByName('txt_itemlist_new{$action_suffix}')[0].value+{if $allow_global}','+document.getElementsByName('chk_itemlist_global{$action_suffix}')[0].checked+{/if}',{$sm.pos},{$sm.sesid}', do_element); return false;" />
{/if}

{if !$hide_table_html}
</td>
</tr>

<tr><td colspan="3"><br /></td></tr>
{/if}
