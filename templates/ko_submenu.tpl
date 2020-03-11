{if !$hideWrappingDiv}
<div class="panel" name="sm_{$sm.mod}_{$sm.id}" id="sm_{$sm.mod}_{$sm.id}" data-sm-mod="{$sm.mod}" data-sm-id="{$sm.id}">
{/if}
	<div class="panel-heading" role="tab" id="sm_{$sm.mod}_{$sm.id}_heading">
		<h4 class="panel-title">
			<a data-toggle="collapse" data-target="#sm_{$sm.mod}_{$sm.id}_content" href="#sm_{$sm.mod}_{$sm.id}_content" aria-expanded="true" data-tostate="{if $sm.state == "open"}closed{else}open{/if}" aria-controls="sm_{$sm.mod}_{$sm.id}_content"
				onclick="
					sendReq('../inc/ajax.php', 'action,id,mod,tostate,sesid', 'togglesm,{$sm.id},{$sm.mod},' + $(this).attr('data-tostate') + ',{$sm.sesid}', do_element);
					if($(this).attr('data-tostate') == 'open')
						$(this).attr('data-tostate', 'closed');
					else
						$(this).attr('data-tostate', 'open');
				" alt="close">
				{$sm.titel}
			</a>
			{if $help.show}{$help.link}{/if}
		</h4>
	</div>
	<div id="sm_{$sm.mod}_{$sm.id}_content" class="panel-collapse collapse{if $sm.state != "closed"} in{/if}" role="tabpanel" aria-labelledby="sm_{$sm.mod}_{$sm.id}_heading">
		<div class="panel-body">
			{if $sm.items.0.type == "notizen"}
				<div class="sidebar-html-container">
					{include file="ko_sm_notizen.tpl"}
				</div>
			{else}
				<div class="submenu">
					{if $sm.form}
					<form action="{if $sm.form_action}{$sm.form_action}{else}index.php{/if}" method="{if $sm.form_method}{$sm.form_method}{else}POST{/if}">
						{foreach from=$sm.form_hidden_inputs item=h}
							<input type="hidden" name="{$h.name}" value="{$h.value}" />
						{/foreach}
					{/if}
						{foreach from=$sm.items item=i}
							{if $i.type == 'link'}
								<ul class="sm-item-link"><li class="{if $i.active}active{/if}{if $i.disabled} disabled{/if}" data-action="{$i.action}"><a {if !$i.disabled}href="{$i.link}"{/if}>{$i.title}{if $i.badge !== null} <span class="badge">{$i.badge}</span>{/if}</a></li></ul>
							{elseif $i.type == 'seperator'}
								{if $i.noLine}
									<br class="sm-item-seperator">
								{else}
									<hr class="sm-item-seperator">
								{/if}
							{elseif $i.type == 'html'}
								{if $i.title}
									<h5 class="sm-item-title">{$i.title}</h5>
								{/if}
								{if $i.html}
									<div class="sidebar-html-container">
										{$i.html}
									</div>
								{/if}
							{elseif $i.type == 'itemlist'}
								<div class="sidebar-html-container">
									{assign var="tpl_itemlist_select" value=$i.tpl_itemlist_select}
									{assign var="tpl_itemlist_values" value=$i.tpl_itemlist_values}
									{assign var="tpl_itemlist_output" value=$i.tpl_itemlist_output}
									{assign var="tpl_itemlist_title" value=$i.tpl_itemlist_title}
									{assign var="tpl_itemlist_selected" value=$i.tpl_itemlist_selected}
									{assign var="taxonomy_filter" value=$i.taxonomy_filter}
									{assign var="room_filter" value=$i.room_filter}
									{assign var="show_sort_cols" value=$i.show_sort_cols}
									{assign var="sort_cols_checked" value=$i.sort_cols_checked}
									{assign var="allow_global" value=$i.allow_global}
									{assign var="action_suffix" value=$i.action_suffix}
									{include file="ko_itemlist.tpl"}
								</div>
							{/if}
						{/foreach}
					{if $sm.form}
					</form>
					{/if}
				</div>
			{/if}
		</div>
	</div>
	{if !$hideWrappingDiv}
</div>
	{/if}
