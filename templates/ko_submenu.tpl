<div name="sm_{$sm.mod}_{$sm.id}" id="sm_{$sm.mod}_{$sm.id}">
<table width="100%" cellspacing="0" cellpadding="0">
	<tr width="100%">
	<td width="12px" height="13px" valign="bottom">
	<ul id="sm">
	<li><span><img src="{$ko_path}images/submenu_dropdown.gif" border="0"></span>
	<ul>
	<li onclick="sendReq('../inc/ajax.php', 'action,pos,id,mod,sesid', 'movesmup,{$sm.position},{$sm.id},{$sm.mod},{$sm.sesid}', do_element);"><a><img src="{$ko_path}images/icon_up_small.gif" border="0" alt="up" />&nbsp;{$label_sm_up}</a></li>
	<li onclick="sendReq('../inc/ajax.php', 'action,pos,id,mod,sesid', 'movesmdown,{$sm.position},{$sm.id},{$sm.mod},{$sm.sesid}', do_element);"><a><img src="{$ko_path}images/icon_down_small.gif" border="0" alt="down" />&nbsp;{$label_sm_down}</a></li>
	{if $sm.state == "closed"}
		<li onclick="sendReq('../inc/ajax.php', 'action,pos,id,mod,sesid', 'opensm,{$sm.position},{$sm.id},{$sm.mod},{$sm.sesid}', do_element);"><a><img src="{$ko_path}images/icon_open_small.gif" border="0" alt="open" />&nbsp;{$label_sm_open}</a></li>
	{else}
		<li onclick="sendReq('../inc/ajax.php', 'action,pos,id,mod,sesid', 'closesm,{$sm.position},{$sm.id},{$sm.mod},{$sm.sesid}', do_element);"><a><img src="{$ko_path}images/icon_close_small.gif" border="0" alt="close" />&nbsp;{$label_sm_close}</a></li>
	{/if}
	{if $sm.position == "left"}
		<li><a href="index.php?action=move_sm_right&amp;pos=left&amp;id={$sm.id}"><img src="{$ko_path}images/icon_right_small.gif" border="0" alt="right" />&nbsp;{$label_sm_right}</a></li>
	{elseif $sm.position == "right"}
		<li><a href="index.php?action=move_sm_left&amp;pos=right&amp;id={$sm.id}"><img src="{$ko_path}images/icon_left_small.gif" border="0" alt="left" />&nbsp;{$label_sm_left}</a></li>
	{/if}
	</ul>
	</li>
	</ul>


	</td><td class="submenu_header">
	{$sm.titel}
	</td>
	<td>
		{if $help.show}{$help.link}{/if}
	</td>
	</tr>

	
	{if $sm.state != "closed"}


		{if $sm.output.0 == "[itemlist]"}
			{include file="ko_itemlist.tpl"}
		{elseif $sm.output.0 == "[notizen]"}
			{include file="ko_sm_notizen.tpl"}
		{else}


			<tr width="100%"><td colspan="3" class="submenu" style="line-height:145%">
			{if $sm.form}
				<form action="{if $sm.form_action}{$sm.form_action}{else}index.php{/if}" method="{if $sm.form_method}{$sm.form_method}{else}POST{/if}">
				{foreach from=$sm.form_hidden_inputs item=h}
					<input type="hidden" name="{$h.name}" value="{$h.value}" />
				{/foreach}
			{/if}

			{foreach from=$sm.output item=i key=i_i}
				{if $i==""}
					<br />
				{else}

					{if $sm.no_ul[$i_i] != TRUE}<strong><big>&middot;</big></strong>{/if}
					{if $i == " "}
						{$i}
					{elseif $sm.link[$i_i] != ""}<a href="{$sm.link[$i_i]}">{$i}</a><br />
					{else}
						{$i}<br />
					{/if}

					{if $sm.html[$i_i]}
						{$sm.html[$i_i]}
					{/if}

				{/if}
			{/foreach}
			{if $sm.form}</form>{/if}
			</td></tr>

			<tr><td colspan="3"><br /></td></tr>
		{/if}

	{/if}
</table>
</div>
