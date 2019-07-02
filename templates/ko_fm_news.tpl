{include file="ko_fm_header.tpl"}

<table width="100%" cellspacing="0">
{foreach from=$tpl_news item=news key=k}

	<tr><td class="news_header">
	{if $tpl_fm_pos == "m"}
		{$news.title}
	{else}
		<a href="index.php?action=show_single_news&amp;id={$news.id}">{$news.title}</a>
	{/if}
	</td></tr>

	<tr><td class="news_content">
	<i>{$news.subtitle}</i>

	{if $tpl_fm_pos == "m"}
		<br /><br />
		{$news.text}
		<br /><br />
		{if $news.link}
			{$label_link}: <a href="http://{$news.link}">{$news.link}</a>
			<br /><br />
		{/if}
		<small>
      {if $news.author}{$news.author}<br />{/if}
      {if $news.cdate}{$news.cdate}{/if}
    </small>
	{else}
		{$news.text|truncate:30:"..."}
	{/if}

	</td></tr>

{/foreach}
</table>


{include file="ko_fm_footer.tpl"}
