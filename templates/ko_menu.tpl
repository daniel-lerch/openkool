<!-- Message-Box for ajax-requests -->
<!-- position:fixed IE-Hack von annevankesteren.nl/test/examples/ie/position-fixed.html -->
<div style="" name="wait_message" id="wait_message"><button class="btn btn-default"><i class="fa fa-spinner fa-pulse icon-line-height"></i></button></div>
<!-- Session timeout warning -->
<div style="visibility:hidden;display:none;padding:6px;margin:5px 180px 10px 10px;background-color:#ffff00;border:3px solid #c80202;position:fixed;_position:absolute;right:0;top:0;_top:expression(eval(document.body.scrollTop));z-index:900;width:180px;text-align:center;" name="session_timeout" id="session_timeout">{ll key="session_timeout"}</div>

<header id="site-header">
	<div class="container-fluid" id="top-header">
		<div class="row">
			<div class="hidden-xs hidden-sm col-md-3" id="kool-text">
				<a href="{$ko_path}index.php">
					<img src="{$ko_path}{$file_logo_small}" border="0" height="50" width="142" alt="kOOL" title="kOOL" />
				</a>
			</div>
			<div class="hidden-xs col-sm-8 col-md-6 text-center-md text-left-xs" id="logo-header">
				{$header_code}
			</div>
			<div class="col-xs-12 col-sm-4 col-md-3 text-right">
				{if $ses_lang && $langs|@count > 1}
					<div class="btn-group" id="lang-select">
						<button type="button" class="btn btn-default navbar-btn{if $langs|@count > 1} dropdown-toggle"{/if} {if $langs|@count > 1}data-toggle="dropdown"{/if} aria-expanded="false">
							{$ses_lang} <span class="caret"></span>
						</button>
						{if $langs|@count > 1}
							<ul class="dropdown-menu dropdown-menu-right" role="menu">
							{foreach from=$langs item="lang"}
								<li{if $lang == $ses_lang} class="active"{/if}><a href="index.php?set_lang={$lang}">{$pre}{strtoupper str=$lang}{$post}</a></li>
							{/foreach}
							</ul>
						{/if}
					</div>
				{/if}

				{if !$ses_username || $ses_username == "ko_guest"}
					<div class="btn-group login">
						<button type="button" class="btn btn-primary navbar-btn dropdown-toggle" id="btn__login" data-toggle="dropdown" aria-expanded="false">
							{ll key="login"} <span class="caret"></span>
						</button>
						<ul id="login-menu" class="dropdown-menu dropdown-menu-right dropdown-form" role="menu">
							<li>
								<form method="post" action="{$ko_path}index.php">
									<div class="form-group">
										<label for="username">{ll key="login_username"}</label>
										<input class="form-control" type="text" name="username" id="username" placeholder="{ll key="login_username"}">
									</div>
									<div class="form-group">
										<label for="password">{ll key="login_password"}</label>
										<input class="form-control" type="password" name="password" id="password" placeholder="{ll key="login_password"}">
									</div>
									<button type="submit" class="btn btn-success" name="Login" value="Login">{ll key="login"}</button>
								</form>
							</li>
						</ul>
					</div>
				{else}
					<div class="btn-group">
						<button type="button" class="btn btn-success navbar-btn dropdown-toggle" id="btn__logout" data-toggle="dropdown" aria-expanded="false">
							{$ses_username} <span class="caret"></span>
							<span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul class="dropdown-menu dropdown-menu-right" role="menu">
						{foreach from=$user_menu item="um_entry"}
							{if $um_entry.type == 'link'}
								<li><a href="{$um_entry.link}">{$um_entry.title}</a></li>
							{else}
								<li class="divider" role="presentation"></li>
							{/if}
						{/foreach}
						</ul>
					</div>
				{/if}
			</div>
		</div>
	</div>


	<div id="navbar-placeholder"></div>
	<nav class="navbar navbar-inverse navbar-static-top" id="navbar-main">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#ko_menu">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<div class="collapse navbar-collapse" id="ko_menu">
				<ul class="nav navbar-nav">
					<li{if $tpl_menu_akt == "home"} class="active"{/if}>
						<a href="/index.php">{ll key="submenu_home_title_home"}</a>
					</li>
					{foreach from=$tpl_menu item=menu}
						<li class="dropdown{if $menu.id == $tpl_menu_akt} active{/if}">
							<a href="{$menu.link}" {if $menu.menu}class="dropdown-toggle" data-toggle="dropdown" {/if}role="button" aria-expanded="false" {$menu.link_param}>{$menu.name}{if $menu.menu || $module_settings_action.$menuId != ''}<span class="caret hidden-xs"></span>{/if}</a>
							{if $menu.menu || $module_settings_action.$menuId != ''}
								<ul class="dropdown-menu" role="menu">
									{assign var="firstSubMenuPoint" value=true}
									{foreach from=$menu.menu item=submenu}
										{if $submenu.link}
											<li class="{if $submenu.active}active{/if} {if $submenu.disabled}disabled{/if}"><a href="{$submenu.link}">{$submenu.title}{if $submenu.badge !== null} <span class="badge">{$submenu.badge}</span>{/if}</a></li>
										{else}
											{if !$firstSubMenuPoint}
												<li class="divider" role="presentation"></li>
											{/if}
											<li role="presentation" class="dropdown-header">{$submenu.title}</li>
										{/if}
										{assign var="firstSubMenuPoint" value=false}
									{/foreach}
									{assign var="menuId" value=$menu.id}
									{if $module_settings_action.$menuId != ''}
										{assign var="menuAction" value=$module_settings_action.$menuId }
										{assign var="menuLLKey" value="submenu_`$menuId`_`$menuAction`"}
										{if !$firstSubMenuPoint}<li class="divider" role="presentation"></li>{/if}
										<li class="{if $menu.id == $tpl_menu_akt && $tpl_action == $menuAction}active{/if}"><a class="clearfix" href="/{$menuId}?action={$menuAction}"><span class="pull-left">{ll key=$menuLLKey}</span><i class="fa fa-cog dropdown-icon"></i></a></li>
									{/if}
								</ul>
							{/if}
						</li>
					{/foreach}
				</ul>
			</div>
		</div>
	</nav>
	{if $tpl_menu_akt != 'home'}
	<nav id="navbar-sec">
		<div class="container-fluid">
			<button type="button" class="sidebar-toggle{if $tpl_sidebar_active} active{/if}" data-toggle="offcanvas" data-target="#sidebar" id="sidebar-toggle-sub">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<ul class="nav navbar-nav sortable" style="padding-right:30px;">
				{foreach from=$tpl_menubar_links item=link}
					<li class="nowrap{if $link.active} active{/if}" data-action="{$link.action}"  data-sm-id="{$link.sm_id}"><a href="{$link.link}">{$link.title}</a></li>
				{/foreach}
			</ul>
			<ul class="nav navbar-nav">
				<li id="shortlink-trash" style="display:none;"><a><i class="fa fa-trash icon-line-height"></i></a></li>
			</ul>
			{if $settings_page}
				<ul class="nav navbar-nav pull-right">
					<li{if $settings_page == $tpl_action || $settings_page == $tpl_ses_show} class="active"{/if}><a href="?action={$settings_page}" title="{ll key="submenu_`$tpl_menu_akt`_`$settings_page`"}"><span class="glyphicon glyphicon-cog icon-line-height"></span></a></li>
				</ul>
			{/if}
			{if $searchbox}
				<ul id="searchbox" class="nav navbar-nav pull-right">
					{include file="ko_searchbox.tpl"}
				</ul>
			{/if}
		</div>
	</nav>
	{/if}
</header>
