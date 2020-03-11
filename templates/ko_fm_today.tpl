{include file="ko_fm_header.tpl"}

{if $show_daten_heute}
	<div class="list-group" style="margin-bottom:0px;">
		<div class="list-group-item list-group-item-info">
			<h5 class="list-group-item-heading nomargin">{$title_event_today} ({$datum_heute})</h5>
		</div>
	{foreach from=$today_daten_heute item=h}
		<div class="list-group-item">
			<h5 class="list-group-item-heading">{$h.eventgruppe}:</h5>
			<i class="fa fa-clock-o"></i> {$h.startzeit}{if $h.endzeit != ""} - {$h.endzeit}{/if}
			{if $h.title != ""}<i class="fa fa-bookmark"></i> "{$h.title}"{/if}
			{if $h.kommentar != ""}<i class="fa fa-comment"></i> "{$h.kommentar}"{/if}
			{if $h.raum != ""}(<i class="fa fa-building"></i> {$h.raum}){/if}
		</div>
	{/foreach}
	</div>
{/if}


{if $show_daten_woche}
	<div class="list-group">
		<div class="list-group-item list-group-item-info">
			{$title_event_week}
		</div>
	{foreach from=$today_daten_woche item=h}
		<div class="list-group-item">
			<h5 class="list-group-item-heading">{$h.wochentag}, {$h.startdatum|truncate:6:""}{if $h.enddatum != ""} - {$h.enddatum|truncate:6:""}{/if}: {$h.eventgruppe}</h5>
			<i class="fa fa-clock-o"></i> {$h.startzeit}{if $h.endzeit != ""} - {$h.endzeit}{/if}
			{if $h.title != ""}<i class="fa fa-bookmark"></i> "{$h.title}"{/if}
			{if $h.kommentar != ""}<i class="fa fa-comment"></i> "{$h.kommentar}"{/if}
			{if $h.raum != ""}(<i class="fa fa-building"></i> {$h.raum}){/if}
		</div>
	{/foreach}
	</div>
{/if}


{if $show_res}
	<div class="list-group">
		<div class="list-group-item list-group-item-info">
			{$title_res_week}:
		</div>
		<div class="list-group-item" id="ko_res_calendar"></div>
	</div>
{/if}



{if $show_res_mod}
	<div class="list-group">
		<div class="list-group-item list-group-item-info">
			{$title_res_new}:
		</div>
	{foreach from=$today_res_mod item=h}
		<div class="list-group-item">
			<h5 class="list-group-item-heading">{$h.wochentag}, {$h.startdatum|truncate:6:""}{if $h.enddatum != ""} - {$h.enddatum|truncate:6:""}{/if}: {$h.item}</h5>
			<i class="fa fa-clock-o"></i> {$h.startzeit}{if $h.endzeit != ""} - {$h.endzeit}{/if}
			{if $h.zweck != ""}"{$h.zweck}"{/if}
			{if $h.name != ""}{$h.name}{/if}
			{if $h.email != "" || $h.telefon != ""}
				({$h.email}, {$h.telefon})
			{/if}
		</div>
	{/foreach}
	</div>
{/if}



{if $show_leute_change}
	<div class="list-group">
		<div class="list-group-item list-group-item-info">
			{$title_people_new}:
		</div>
	{foreach from=$today_leute_change item=d}
		<div class="list-group-item">
			<h5 class="list-group-item-heading">{if $d.link}<a href="{$d.link}">{/if}{$d.name}{if $d.link}</a>{/if}:<small><i>({$d.user})</i>&nbsp;{$d.log}</small></h5>
		</div>
	{/foreach}
	</div>
{/if}



{include file="ko_fm_footer.tpl"}
