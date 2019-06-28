{if $person.firm}
	<div class="single_view_title">{$person.firm}
	{if $person.department}<i>{$person.department}</i>{/if}
	<div class="single_view_subtitle">{$person.vorname} {$person.nachname}</div>
{else}
	<div class="single_view_title">{$person.vorname} {$person.nachname}</div>
{/if}

{$person.vorname} {$person.nachname}<br />
{if $person.adresse}{$person.adresse}<br />{/if}
{if $person.adresse_zusatz}{$person.adresse_zusatz}<br />{/if}
{if $person.plz}{$person.plz} {/if}{$person.ort}<br />

<br />
<b>TODO:</b> Mehr Infos: Gruppen inkl. Datenfelder, Familie, KG, usw.

<br /><br />
<a href="javascript:history.back();">&larr; zurück</a>
