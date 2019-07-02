<div style="text-align: center;">
{foreach from=$tpl_years item=year}
<a href="index.php?action=set_stats_year&amp;year={$year}">{$year}</a>&nbsp;&nbsp;
{/foreach}
</div>


<h1>{$table_year_title}</h1>

<table class="donations-stats" cellpadding="0" cellspacing="0">
<tr>
<td class="donations-stats-header dark bottom right">{$cur_year}</td>
{foreach from=$tpl_header item=header}
	<td colspan="2" class="donations-stats-header dark bottom">{$header}</td>
{/foreach}
<td colspan="2" class="donations-stats-header dark bottom left">{$label_total}</td>
</tr>

{foreach from=$tpl_data.accounts item=data}
	<tr><td class="donations-stats-account dark right" id="donation_account_{$data.id}">{$data.name}</td>
	{section name=month start=1 loop=$data max=12 step=1}
		<td class="donations-stats-content-amount">
			{if $data[month].amount > 0}
				{$data[month].amount|number_format:2:".":"'"}
			{else}
				&nbsp;
			{/if}
		</td>
		<td class="donations-stats-content-num">
			&nbsp;
			{if $show_num && $data[month].donations > 0}
				({$data[month].donations})
			{/if}
		</td>
	{/section}
	<td class="donations-stats-content-amount dark">
		{if $data.total.amount > 0}
			{$data.total.amount|number_format:2:".":"'"}
		{else}
			&nbsp;
		{/if}
	</td>
	<td class="donations-stats-content-num dark left">
		&nbsp;
		{if $show_num && $data.total.donations > 0}
			({$data.total.donations})
		{else}
			&nbsp;
		{/if}
	</td>
	</tr>

	{foreach from=$data.sources item=source}
		<tr class="donation_account_source source_account_{$data.id}"><td class="donations-stats-source right">{$source.name}</td>
		{section name=month start=1 loop=$data max=12 step=1}
			<td class="donations-stats-source-amount">
				{if $source[month].amount > 0}
					{$source[month].amount|number_format:2:".":"'"}
				{else}
					&nbsp;
				{/if}
			</td>
			<td class="donations-stats-source-num">
				&nbsp;
				{if $show_num && $source[month].num > 0}
					({$source[month].num})
				{/if}
			</td>
		{/section}
		<td class="donations-stats-source-amount">
			{if $source.total.amount > 0}
				{$source.total.amount|number_format:2:".":"'"}
			{else}
				&nbsp;
			{/if}
		</td>
		<td class="donations-stats-source-num left">
			&nbsp;
			{if $show_num && $source.total.num > 0}
				({$source.total.num})
			{/if}
		</td>
		</tr>
	{/foreach}

{/foreach}

<tr>
<td class="donations-stats-account dark top right">{$label_total}</td>
{foreach from=$tpl_data.total item=total}
	<td class="donations-stats-content-amount dark top">
		{if $total.amount > 0}
			{$total.amount|number_format:2:".":"'"}
		{else}
			&nbsp;
		{/if}
	</td>
	<td class="donations-stats-content-num dark top">
		{if $show_num && $total.donations > 0}
			&nbsp;({$total.donations})
		{/if}
	</td>
{/foreach}
<td class="donations-stats-content-amount dark top">{$tpl_data.grand_total.amount|number_format:2:".":"'"}</td>
<td class="donations-stats-content-num dark top left">{if $show_num}&nbsp;({$tpl_data.grand_total.donations}){/if}</td>
</tr>
</table>

<br />
<h1>{$img_year_title}</h1>
{$img_year}
