{include file="ko_fm_header.tpl"}

<div class="formular_header">
	<label>{ll key="kota_ko_leute_info_rota_2"}</label>
</div>
{$tpl_rota}

{if $tpl_ical != ""}
	<p>{$tpl_ical}</p>
{/if}

{if $tpl_consensus != ""}
	<a href="{$tpl_consensus}" target="_blank">{ll key="rota_placeholder_CONSENSUS_LINK"}</a>
{/if}

{include file="ko_fm_footer.tpl"}
