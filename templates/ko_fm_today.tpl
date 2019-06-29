{include file="ko_fm_header.tpl"}

<table width="100%" cellspacing="0">

{if $show_daten_heute}
	<tr><td class="news_header">
	{$title_event_today} ({$datum_heute}):
	</td></tr>

	<tr><td class="news_content">
	{foreach from=$today_daten_heute item=h}
		{if $tpl_fm_pos == "m"}
			<strong><big>&middot;</big></strong>&nbsp;<b>{$h.eventgruppe}:</b>
			{$h.startzeit}{if $h.endzeit != ""} - {$h.endzeit}{/if}
			{if $h.kommentar != ""}"{$h.kommentar}"{/if}
			{if $h.raum != ""}({$h.raum}){/if}
			<br />
		{else}
			<strong><big>&middot;</big></strong>&nbsp;<b>{$h.eventgruppe}:</b><br />
			{$h.startzeit}
			<br />
		{/if}
	{/foreach}
	</td></tr>
{/if}


{if $show_daten_woche}
	<tr><td class="news_header">
	{$title_event_week}:
	</td></tr>

	<tr><td class="news_content">
	{foreach from=$today_daten_woche item=h}
		{if $tpl_fm_pos == "m"}
			<strong><big>&middot;</big></strong>&nbsp;<b>{$h.wochentag}, {$h.startdatum|truncate:6:""}{if $h.enddatum != ""} - {$h.enddatum|truncate:6:""}{/if}: {$h.eventgruppe} </b>
			{$h.startzeit}{if $h.endzeit != ""} - {$h.endzeit}{/if}
			{if $h.kommentar != ""}"{$h.kommentar}"{/if}
			{if $h.raum != ""}({$h.raum}){/if}
			<br />
		{else}
			<strong><big>&middot;</big></strong>&nbsp;<b>{$h.wochentag}, {$h.startdatum|truncate:6:""}:</b><br />
			{$h.eventgruppe} ({$h.startzeit})
			<br />
		{/if}
	{/foreach}
	</td></tr>
{/if}



{if $show_daten_heute || $show_daten_woche}
	<tr><td><br /></td></tr>
{/if}




{if $show_res}
	<tr><td class="news_header">
	{$title_res_week}:
	</td></tr>

	<tr><td class="news_content">
	{foreach from=$today_res_woche item=h}
		{if $tpl_fm_pos == "m"}
			<strong><big>&middot;</big></strong>&nbsp;<b>{$h.wochentag}, {$h.startdatum|truncate:6:""}{if $h.enddatum != ""} - {$h.enddatum|truncate:6:""}{/if}: {$h.item} </b>
			{$h.startzeit}{if $h.endzeit != ""} - {$h.endzeit}{/if}
			{if $h.zweck != ""}"{$h.zweck}"{/if}
			<br />
		{else}
			<strong><big>&middot;</big></strong>&nbsp;<b>{$h.wochentag}, {$h.startdatum|truncate:6:""}:</b><br />
			{$h.item} ({$h.startzeit})
			<br />
		{/if}
	{/foreach}
	</td></tr>
{/if}



{if $show_res_mod}
	<tr><td class="news_header">
	{$title_res_new}:
	</td></tr>

	<tr><td class="news_content">
	{foreach from=$today_res_mod item=h}
		{if $tpl_fm_pos == "m"}
			<strong><big>&middot;</big></strong>&nbsp;<b>{$h.wochentag}, {$h.startdatum|truncate:6:""}{if $h.enddatum != ""} - {$h.enddatum|truncate:6:""}{/if}: {$h.item} </b>
			{$h.startzeit}{if $h.endzeit != ""} - {$h.endzeit}{/if}
			{if $h.zweck != ""}"{$h.zweck}"{/if}
			{if $h.name != ""}{$h.name}{/if}
			{if $h.email != "" || $h.telefon != ""}
				({$h.email}, {$h.telefon})
			{/if}
			<br />
		{else}
			<strong><big>&middot;</big></strong>&nbsp;<b>{$h.wochentag}, {$h.startdatum|truncate:6:""}:</b><br />
			{$h.item} ({$h.startzeit})
			<br />
		{/if}
	{/foreach}
	</td></tr>
{/if}



{if $show_res || $show_res_mod}
	<tr><td><br /></td></tr>
{/if}


{if $show_leute_change}
	<tr><td class="news_header">
	{$title_people_new}:
	</td></tr>

	<tr><td class="news_content">
	{foreach from=$today_leute_change item=d}
		<b>{if $d.link}<a href="{$d.link}">{/if}{$d.name}{if $d.link}</a>{/if}:</b>&nbsp;<i>({$d.user})</i>&nbsp;{$d.log}<br />
	{/foreach}
	</td></tr>
{/if}


{if $show_leute_change}
	<tr><td><br /></td></tr>
{/if}


</table>


{include file="ko_fm_footer.tpl"}
