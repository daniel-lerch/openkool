{include file="ko_fm_header.tpl"}

<ul class="list-group" style="margin-bottom:0px;">

{foreach from=$tpl_news item=news key=k}

	<li class="list-group-item">

	<h4 class="list-group-item-heading">
		{$news.title} <small>{$news.subtitle}</small>
	</h4>

	<p class="list-group-item-text">
		{$news.text}
	</p>
	{if $news.link}
		{$label_link}: <a href="http://{$news.link}">{$news.link}</a>
	{/if}
	{if $news.author || $news.cdate}<br><br>{/if}
	{if $news.author}<i class="fa fa-user"></i> {$news.author}{/if}
	{if $news.cdate}<span class="pull-right"><i class="fa fa-clock-o"></i> {$news.cdate}</span>{/if}

	</li>

{/foreach}
</ul>


{include file="ko_fm_footer.tpl"}
