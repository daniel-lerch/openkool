{if $data && $data|@sizeof > 0 && !$tooManyResults}
	{foreach from=$data key="famid" item="famInfo"}
		{if $famInfo && $famInfo|@sizeof > 0}
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title"><span class="pull-left" style="display:block;padding-top:5px;">{$famInfo.family}</span><i class="clearfix"></i></h4>
				</div>
				<div class="list-group">
					{foreach from=$famInfo.persons key="personId" item="person"}
						<a href="#" class="checkin-search-result list-group-item{if $person._info.checked_in} checked-in{/if}"{if $person._info.checked_in} title="{ll key="checkin_label_checked_in"}" data-checkout-confirm="{$person._info.checkout_confirm}"{/if} data-person-id="{$person.id}">
							<table class="full-width" style="vertical-align: middle;"><tr><td>{$person.desc}</td><td style="vertical-align: middle;font-size:25px;" width="1%"><i class="checked-indicator fa fa-{if $person._info.checked_in}check-square-o{else}square-o{/if}"></i></td></tr></table>
						</a>
					{/foreach}
				</div>
			</div>
		{/if}
	{/foreach}
	<button type="button" class="btn btn-primary btn-sm checkin-selected-btn pull-right" disabled >{ll key="checkin_label_checkin_selected_btn"}</button>
{elseif $tooManyResults}
	<div class="panel panel-default">
		<div class="panel-body">
			{ll key="checkin_label_too_many_results"}
		</div>
	</div>
{else}
	<div class="panel panel-default">
		<div class="panel-body">
			{ll key="checkin_label_no_results"}
		</div>
	</div>
{/if}
