{include file="ko_fm_header.tpl"}


<!-- Reservationen: -->
{if $tpl_show_res}
	{if $tpl_open_mod_res}
		<a href="reservation/index.php?action=show_mod_res" class="fm_mod_highlight">
	{/if}
	{$tpl_text_res}
	{if $tpl_open_mod_res}
		</a>
	{/if}
{/if}

<!-- Adressänderungen: -->
{if $tpl_show_aa}
	{if $tpl_open_mod_aa}
		<a href="leute/index.php?action=show_aa" class="fm_mod_highlight">
	{/if}
	{$tpl_text_aa}
	{if $tpl_open_mod_aa}
		</a>
	{/if}
{/if}

<!-- Gruppen-Anmeldungen: -->
{if $tpl_show_groups}
	{if $tpl_open_mod_groups}
		<a href="leute/index.php?action=show_groupsubscriptions" class="fm_mod_highlight">
	{/if}
	{$tpl_text_groups}
	{if $tpl_open_mod_groups}
		</a>
	{/if}
{/if}

<!-- Events: -->
{if $tpl_show_event}
	{if $tpl_open_mod_event}
		<a href="daten/index.php?action=list_events_mod" class="fm_mod_highlight">
	{/if}
	{$tpl_text_event}
	{if $tpl_open_mod_event}
		</a>
	{/if}
{/if}

<!-- Newsletter: -->
{if $tpl_show_nl}
	{if $tpl_open_mod_nl}
		<a href="leute/index.php?action=show_nl" class="fm_mod_highlight">
	{/if}
	{$tpl_text_nl}
	{if $tpl_open_mod_nl}
		</a>
	{/if}
{/if}


{include file="ko_fm_footer.tpl"}
