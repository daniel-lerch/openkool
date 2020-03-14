<h3>
	{ll key="groups_list_search_results_title"}
</h3>
<ul class="list-group">
	{assign var="first" value=1}
	{foreach from=$results item="result"}
		<ol class="breadcrumb no-margin list-group-item list-group-item-default">
			{foreach from=$result.groups item="group"}
				<li><a href="{$group.link}">{$group.name}</a></li>
			{/foreach}
		</ol>
		{assign var="first" value=0}
	{/foreach}
</ul>
