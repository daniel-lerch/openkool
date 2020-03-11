{include file="ko_fm_header.tpl"}

<ul class="list-group" style="margin-bottom:0px;">

{if $categories}
	<div class="news-filter pull-right">
		{ll key="news_filter"}
		{foreach from=$categories item=cat}
			<a class="label label-default" data-cat="{$cat}">{$cat}</a>
		{/foreach}
	</div>
	<br clear="all" /><br />
{/if}

{foreach from=$tpl_news item=news key=k}

	<li class="list-group-item news-item" data-cat="{$news.category}">

		{if !$is_guest}
			<div class="news-status pull-right">
				{if $news.statusRead == 0}
					<a href="index.php?action=news_status&status=1&id={$news.id}" class="news-status-new" title="{ll key="news_mark_read"}"><i class="fa fa-check"></i>&nbsp;{ll key="news_mark_new"}</a>
				{else}
					<a href="index.php?action=news_status&status=0&id={$news.id}" class="news-status-read" data-id="{$news.id}">{ll key="news_mark_read"}</a>
				{/if}
				<br />
				{$news.category}
			</div>
		{/if}

		<h4 class="list-group-item-heading">
			{$news.title}<br />
			<small>{$news.subtitle}</small>
		</h4>

		<div class="collapse {if $news.statusRead == 0}in{/if}">
			<div class="list-group-item-text">
				{$news.text}
			</div>
			{if $news.link}
				<div class="news-link">
					{$label_link}: <a href="{$news.link}" target="_blank">{$news.link}</a>
				</div>
			{/if}
			{if $news.author || $news.cdate}
				<div class="news-author-date">
					{if $news.author}<i class="fa fa-user"></i> {$news.author}{/if}
					{if $news.cdate}<span class="pull-right"><i class="fa fa-clock-o"></i> {$news.cdate}</span>{/if}
				</div>
			{/if}
		</div>

	</li>

{/foreach}
</ul>


{include file="ko_fm_footer.tpl"}
