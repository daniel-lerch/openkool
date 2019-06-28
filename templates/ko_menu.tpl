<div class="menu">

<ul id="nav">
<li><a href="{$ko_path}index.php" class="first">Home</a></li>
{foreach from=$tpl_menu item=menu}
	{if $tpl_menu_akt == $menu.id}
		{assign var="post_akt" value="1"}
		{assign var="a_class" value="akt"}
		{assign var="li_class" value="akt"}
	{else}
		{assign var="a_class" value=""}
		{assign var="li_class" value=""}
		{if $post_akt == 1}
			{assign var="a_class" value="post_akt"}
		{/if}
	{/if}

	{if $menu.menu}
		{assign var="li_class2" value="menuparent"}
	{/if}

	<li class="{$li_class} {$li_class2}"><a href="{$menu.link}" {$menu.link_param} class="{$a_class}">{$menu.name}</a>
	{if $menu.menu}
		<ul>
		{foreach from=$menu.menu item=submenu}
			{if $submenu.link}
				<li><a href="{$submenu.link}">{$submenu.name}</a></li>
			{else}
				<li class="header">{$submenu.name}</li>
			{/if}
		{/foreach}
		</ul>
	{/if}

	{if $tpl_menu_akt != $menu.id AND $post_akt == 1}
		{assign var="post_akt" value="0"}
	{/if}

	</li>
{/foreach}

</ul>

</div>
<br clear="all" />
