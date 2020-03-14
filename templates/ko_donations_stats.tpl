<h3>
	{$table_year_title}
	<div class="btn-group btn-group-sm pull-right">
		{foreach from=$tpl_years item=year}
			<a class="btn btn-default" href="index.php?action=set_stats_year&amp;year={$year}"{if $year == $cur_year} disabled{/if}>
				{$year}
			</a>
		{/foreach}
	</div>
</h3>

<style>
	#donations-stats-accounts-chart .ct-chart-bar .ct-label.ct-horizontal.ct-end {ldelim}
		display: block;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		transform: rotate(-40deg);
		transform-origin: 100% 0;
		text-align: right;
		max-height: 1.5em;
		min-width: 100px;
		max-width: 100px;
	{rdelim}
</style>

<table class="table table-bordered" style="width:100%;">
	<tr>
		<th class="bottom right bg-primary">{$cur_year}</th>
		{foreach from=$tpl_header item=header}
			<th class="bottom bg-primary text-right">{$header}</th>
		{/foreach}
		<th class="bottom left bg-primary text-right">{$label_total}</th>
	</tr>

	{foreach from=$tpl_data.accounts item=data}
		<tr>
			<td class="donations-stats-account right bg-primary" id="donation_account_{$data.id}">
				{$data.name} <i id="donation_account_{$data.id}_toggle_sign" class="fa fa-plus"></i>
			</td>
			{section name=month start=1 loop=$data max=12 step=1}
				<td class="text-right">
					{if $data[month].amount > 0}
						{$data[month].amount|number_format:2:".":"'"}
					{else}
						&nbsp;
					{/if}
					{if $show_num && $data[month].donations > 0}
						<span class="donations-stats-num">({$data[month].donations})</span>
					{/if}
				</td>
			{/section}
			<td class="bg-primary text-right">
				{if $data.total.amount > 0}
					{$data.total.amount|number_format:2:".":"'"}
				{else}
					&nbsp;
				{/if}
				{if $show_num && $data.total.donations > 0}
					<span class="donations-stats-num">({$data.total.donations})</span>
				{else}
					&nbsp;
				{/if}
			</td>
		</tr>

		{foreach from=$data.sources item=source}
			<tr class="donation_account_source source_account_{$data.id}">
				<td class="right bg-warning">
					{$source.name}
				</td>
				{section name=month start=1 loop=$data max=12 step=1}
					<td class="text-right">
						{if $source[month].amount > 0}
							{$source[month].amount|number_format:2:".":"'"}
						{else}
							&nbsp;
						{/if}
						{if $show_num && $source[month].num > 0}
							<span class="donations-stats-num">({$source[month].num})</span>
						{/if}
					</td>
				{/section}
				<td class="text-right bg-warning">
					{if $source.total.amount > 0}
						{$source.total.amount|number_format:2:".":"'"}
					{else}
						&nbsp;
					{/if}
					{if $show_num && $source.total.num > 0}
						<span class="donations-stats-num">({$source.total.num})</span>
					{/if}
				</td>
			</tr>
		{/foreach}

	{/foreach}

	<tr>
		<td class="donations-stats-account top right bg-primary" id="donation_account_total">
			{$label_total} <i id="donation_account_total_toggle_sign" class="fa fa-plus"></i>
		</td>
		{section name=month start=1 loop=$data max=12 step=1}
			{assign var="total" value=$tpl_data.total[month]}
			<td class="top text-right">
				{if $total.amount > 0}
					{$total.amount|number_format:2:".":"'"}
				{else}
					&nbsp;
				{/if}
				{if $show_num && $total.donations > 0}
					<span class="donations-stats-num">({$total.donations})</span>
				{/if}
			</td>
		{/section}
		<td class="top bg-primary text-right">
			{$tpl_data.grand_total.amount|number_format:2:".":"'"}
			{if $show_num}
				<span class="donations-stats-num">({$tpl_data.grand_total.donations})</span>
			{/if}
		</td>
	</tr>

	{foreach from=$tpl_data.total.sources item=source}
		<tr class="donation_account_source source_account_total">
			<td class="right bg-warning">
				{$source.name}
			</td>
			{section name=month start=1 loop=$data max=12 step=1}
				<td class="text-right">
					{if $source[month].amount > 0}
						{$source[month].amount|number_format:2:".":"'"}
					{else}
						&nbsp;
					{/if}
					{if $show_num && $source[month].num > 0}
						<span class="donations-stats-num">({$source[month].num})</span>
					{/if}
				</td>
			{/section}
			<td class="text-right bg-warning">
				{if $source.total.amount > 0}
					{$source.total.amount|number_format:2:".":"'"}
				{else}
					&nbsp;
				{/if}
				{if $show_num && $source.total.num > 0}
					<span class="donations-stats-num">({$source.total.num})</span>
				{/if}
			</td>
		</tr>
	{/foreach}
</table>

<div class="row">
	<div class="col-sm-6">
		<h3>{$img_year_title}</h3>
		<div class="fullscreen-elem" id="donations-stats-year-chart" style="height:500px;"></div>
		<script>
			var year_data = {$year_data_js};
		</script>
	</div>
	<div class="col-sm-6">
		<h3>{$img_accounts_title}</h3>
		<div class="fullscreen-elem" id="donations-stats-accounts-chart" style="height:500px;"></div>
		<script>
			var accounts_data = {$accounts_data_js};
		</script>
	</div>
</div>
<script>
	draw_donations_charts();
</script>
