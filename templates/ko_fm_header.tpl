<div class="panel panel-default panel-front-module">
	<div class="panel-heading">
		<h4 class="panel-title">
			{if !$tpl_guest}<a style="display:block" data-toggle="collapse" href="#{$tpl_module}-content" data-tostate="{if $tpl_state == 'closed'}open{else}closed{/if}"
			   onclick="
					   sendReq('inc/ajax.php', 'action,fm,tostate,sesid', 'togglefm,{$tpl_module},' + $(this).attr('data-tostate') + ','+kOOL.sid, do_element);
					   if($(this).attr('data-tostate') == 'open')
					   $(this).attr('data-tostate', 'closed');
					   else
					   $(this).attr('data-tostate', 'open');
					   "
					>
				{/if}
				{$tpl_fm_title}
				{if !$tpl_guest}</a>{/if}
		</h4>
	</div>
	{if !$tpl_guest}<div id="{$tpl_module}-content" class="panel-collapse collapse {if $tpl_state != 'closed'}in{/if}">{/if}
		<div class="panel-body">