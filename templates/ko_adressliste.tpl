<div align="right">
{$tpl_stats}
&nbsp;&nbsp;&nbsp;

{if $tpl_prevlink_link neq ""}
	<a href="{$tpl_prevlink_link}">
	<img src="{$ko_path}images/icon_arrow_left.png" border="0" alt="back" />
	</a>
{else}
	<img src="{$ko_path}images/icon_arrow_left_disabled.png" border="0" alt="back" />
{/if}

{if $tpl_nextlink_link neq ""}
	<a href="{$tpl_nextlink_link}">
	<img src="{$ko_path}images/icon_arrow_right.png" border="0" alt="next" />
	</a>
{else}
	<img src="{$ko_path}images/icon_arrow_right_disabled.png" border="0" alt="next" />
{/if}

</div>

{foreach from=$tpl_list_data item=l}
	<fieldset>
	<legend><b>{$l.daten.2}</b>&nbsp;
	<a href="inc/vcard.php?id={$l.vcard_id}"><img src="{$ko_path}images/icon_vcard.png" border="0" alt="vcard" title="vcard" /></a>
	&nbsp;
	{$l.maplinks}
	</legend>
	{foreach from=$l.daten item=row}
		{if $row|strstr:"@"}<a href="mailto:{$row}">{$row}</a><br />
		{elseif $row|replace:" ":"" != ""}{$row}<br />{/if}
	{/foreach}
	</fieldset><br />
{/foreach}
