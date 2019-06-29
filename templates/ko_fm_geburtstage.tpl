{include file="ko_fm_header.tpl"}


{if $tpl_fm_pos == 'm'}
	<table cellpadding="4">
{else}
	<table width="100%">
{/if}

{foreach from=$people item=l}
<tr>

{if $tpl_fm_pos == 'm'}
	<td>{$l.deadline}</td>
	<td><a href="{$l._link}">{$l.vorname} {$l.nachname}</a></td>
	<td>{$l.alter} {$label_years}</td>
	<td>{$l.geburtsdatum}</td>
{else}
	<td>{$l.deadline}</td>
	<td><a href="{$l._link}" onmouseover="tooltip.show('{$l._tooltip}','','b','{$ttpos}')" onmouseout="tooltip.hide()">
	{$l.vorname} {$l.nachname}</a>
	</td>
{/if}

</tr>
{/foreach}
</table>


{include file="ko_fm_footer.tpl"}
