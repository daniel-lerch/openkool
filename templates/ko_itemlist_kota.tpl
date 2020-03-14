<div class="itemlist-container">

	<h5>{$itemlist_open_preset}</h5>
	<div class="input-group input-group-sm">
		<select class="input-sm form-control" name="sel_itemlist{$table}" size="0" onchange="sendReq('../inc/ajax.php', 'action,table,name,sesid', 'kotaitemlistopen,{$table},'+this.options[this.selectedIndex].value+',{$sm.sesid}', do_element);{$itemlist_open_post}">
			<option value=""></option>
			<option value="_all_">{$itemlist_preset_all}</option>
			<option value="_none_">{$itemlist_preset_none}</option>
			<option value="" disabled>----------</option>
			{foreach from=$tpl_itemlist_values item=v key=k}
				<option value="{$v}" {if $v == $tpl_itemlist_selected}selected="selected"{/if}>{$tpl_itemlist_output.$k}</option>
			{/foreach}
		</select>
		{if !$ko_guest}
			<div class="input-group-btn">
				<button class="btn btn-default" alt="{$itemlist_delete_preset}" title="{$itemlist_delete_preset}" onclick="c = confirm('{$itemlist_delete_preset_confirm}');if(!c) return false; sendReq('../inc/ajax.php', 'action,table,name,sesid', 'kotaitemlistdelete,{$table},'+document.getElementsByName('sel_itemlist{$table}')[0].options[document.getElementsByName('sel_itemlist{$table}')[0].selectedIndex].value+',{$sm.sesid}', do_element); return false;"><i class="fa fa-trash"></i></button>
			</div>
		{/if}
	</div>


	<div class="itemlist">
		{foreach from=$tpl_itemlist_select item=s}
		{assign var="close_div" value=""}

		<div class="itemlist_item">
			{if $s.name|truncate:3:"" == "---"}
				<b>{$s.name}</b>
			{elseif $s.type == "group"}
				<input type="checkbox" name="chk_itemlist_group_{$s.value}" id="chk_itemlist_group_{$table}_{$s.value}" value="{$s.value}" title="{$s.name|strip_tags}" {$s.params} {if $s.aktiv} checked="checked"{/if} onclick="sendReq('../inc/ajax.php', 'action,table,id,state,sesid', 'kotaitemlistgroup,{$table},{$s.value},'+this.checked+',{$sm.sesid}', do_element);{if $s.onclick_post != ''}{$s.onclick_post}{else}{$item_onclick_post}{/if}">
				<span class="itemlist_item__link" onclick="sendReq('../inc/ajax.php', 'action,table,id,state,sesid', 'kotaitemlisttogglegroup,{$table},{$s.value},{$s.open},{$sm.sesid}', do_element);change_vis('itemlist_group_{$table}_{$s.value}');s=document.getElementById('span_itemlist_group_{$table}_{$s.value}');{literal}if(s.className=='itemlist_group_0'){s.className='itemlist_group_1'}else{s.className='itemlist_group_0'}{/literal};">
					<span class="itemlist_group_{$s.open}" id="span_itemlist_group_{$table}_{$s.value}">{$s.name}{$closed}</span>
				</span>
			{elseif $s.type == 'html'}
				{$s.html}
			{else}
				{if $s.parent}&nbsp;{/if}
				<label style="width:100%;" for="itemlist_{$table}_{$s.value}">
					<input type="checkbox" name="itemlist_{$s.value}" id="itemlist_{$table}_{$s.value}" value="{$s.value}" title="{$s.name}" {$s.params} {if $s.aktiv} checked="checked"{/if}
						   onclick="sendReq('../inc/ajax.php', 'action,table,id,state,sesid', 'kotaitemlist,{$table},{$s.value},'+this.checked+',{$sm.sesid}', do_element);{if $s.onclick_post != ''}{$s.onclick_post}{else}{$item_onclick_post}{/if}">
					{$s.prename} {$s.name}
				</label>
				{if $s.last}{assign var="close_div" value="</div>"}{/if}
			{/if}
		</div>

		{if $s.type == "group"}
		<div id="itemlist_group_{$table}_{$s.value}" {if $s.open == 0}style="display: none; visibility: hidden;"{/if}>
			{/if}
			{$close_div}
			{/foreach}
		</div>


		{if $show_sort_cols}
			<input type="checkbox" name="chk_sort_cols" id="chk_sort_cols" value="1" {$sort_cols_checked} onclick="sendReq('../inc/ajax.php', 'action,table,state,sesid', 'kotaitemlistsort,{$table},'+this.checked+',{$sm.sesid}', do_element);" /><label for="chk_sort_cols">{$itemlist_sortcols}</label>
		{/if}

		{if !$ko_guest}
			{uid loc="uniqueId"}
			<h5>{$itemlist_save_preset}</h5>
			{if $allow_global} <div class="checkbox"><label for="chk_itemlist_global{$table}"><input type="checkbox" id="chk_itemlist_global{$table}" name="chk_itemlist_global{$table}" value="1"> {$itemlist_global}</label></div> {/if}
			<div class="input-group input-group-sm">
				<input type="text"  class="form-control" name="txt_itemlist_new{$table}">
				<div class="input-group-btn">
					<button class="btn btn-default" type="button" id="save_itemlist_{$table}" alt="{$itemlist_save_preset}" title="{$itemlist_save_preset}" onclick="sendReq('../inc/ajax.php', ['action','table','name','logins'{if $allow_global},'global'{/if},'sesid'], ['kotaitemlistsave','{$table}',document.getElementsByName('txt_itemlist_new{$table}')[0].value,document.getElementsByName('{$uniqueId}-chk-filter-for-logins')[0].value{if $allow_global},document.getElementsByName('chk_itemlist_global{$table}')[0].checked{/if},'{$sm.sesid}'], do_element); return false;">
						<i class="fa fa-save"></i>
					</button>
				</div>
				<div class="input-group-btn">
					<button type="button" class="btn btn-default" onclick="change_vis('{$uniqueId}-filter-for-logins');" alt="options"><i class="fa fa-arrow-down"></i></button>
				</div>
			</div>

			<div name="{$uniqueId}-filter-for-logins" id="{$uniqueId}-filter-for-logins" style="display:none;visibility:hidden;">
				<h5 for="{$uniqueId}-chk-filter-for-logins">{ll key="leute_filter_save_for_logins"}</h5>
				{presetForLogins var="input" name="`$uniqueId`-chk-filter-for-logins"}
				{koPath var="koPath"}
				{include file="`$koPath`templates/ko_formular_elements.tmpl"}
			</div>
		{/if}

		{if !$hide_table_html}
	</div>
	{/if}
</div>
