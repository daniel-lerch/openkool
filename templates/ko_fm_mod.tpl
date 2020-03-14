{include file="ko_fm_header.tpl"}


<!-- Reservationen: -->
{if $tpl_show_res}
	<div style="display:block;">
	{if $tpl_open_mod_res}
		<a href="reservation/index.php?action=show_mod_res">
	{/if}
	{$tpl_text_res}
	{if $tpl_open_mod_res}
		&nbsp;<span class="badge badge-warning">{$tpl_num_res}</span>
		</a>
	{/if}
	</div>
{/if}

<!-- Events: -->
{if $tpl_show_event}
		<div style="display:block;">
	{if $tpl_open_mod_event}
		<a href="daten/index.php?action=list_events_mod">
	{/if}
	{$tpl_text_event}
	{if $tpl_open_mod_event}
		&nbsp;<span class="badge badge-warning">{$tpl_num_event}</span>
		</a>
	{/if}
		</div>
{/if}

<!-- Gruppen-Anmeldungen: -->
{if $tpl_show_groups}
	<div style="display:block;">
	{if $tpl_open_mod_groups}
		<a href="leute/index.php?action=groupsubscriptions">
	{/if}
	{$tpl_text_groups}
	{if $tpl_open_mod_groups}
		&nbsp;<span class="badge badge-warning">{$tpl_num_groups}</span>
		</a>
	{/if}
	</div>
{/if}

<!-- Adressänderungen: -->
{if $tpl_show_aa}
<div style="display:block;">
	{if $tpl_open_mod_aa}
		<a href="leute/index.php?action=show_aa">
	{/if}
	{$tpl_text_aa}
	{if $tpl_open_mod_aa}
		&nbsp;<span class="badge badge-warning">{$tpl_num_aa}</span>
		</a>
	{/if}
</div>
{/if}

<!-- Donations: -->
{if $tpl_show_donations}
		<div style="display:block;">
	{if $tpl_open_mod_donations}
		<a href="donations/index.php?action=list_donations_mod">
	{/if}
	{$tpl_text_donations}
	{if $tpl_open_mod_donations}
		&nbsp;<span class="badge badge-warning">{$tpl_num_donations}</span>
		</a>
	{/if}
		</div>
{/if}

<!-- Newsletter: -->
{if $tpl_show_nl}
			<div style="display:block;">
	{if $tpl_open_mod_nl}
		<a href="leute/index.php?action=show_nl">
	{/if}
	{$tpl_text_nl}
	{if $tpl_open_mod_nl}
		&nbsp;<span class="badge badge-warning">{$tpl_num_nl}</span>
		</a>
	{/if}
			</div>
{/if}


{include file="ko_fm_footer.tpl"}
