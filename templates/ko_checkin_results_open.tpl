{if $data && $data|@sizeof > 0}
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-6 col-sm-6">

				{foreach from=$data key="counter" item="person" name="people"}
					{if $smarty.foreach.people.index == $smarty.foreach.people.total/2|ceil}
						</div>
						<div class="col-md-6 col-sm-6">
					{/if}
					<a href="#" class="checkin-search-result list-group-item{if $person._info.checked_in} checked-in active{/if}"{if $person._info.checked_in} title="{ll key="checkin_label_checked_in"}" data-checkout-confirm="{$person._info.checkout_confirm}"{/if} data-person-id="{$person.id}">
					<table class="full-width" style="vertical-align: middle;"><tr><td>{$person.desc}</td><td style="vertical-align: middle;font-size:25px;" width="1%"><i class="checked-indicator fa fa-{if $person._info.checked_in}check-square-o{else}square-o{/if}"></i></td></tr></table>
					</a>
				{/foreach}
			</div>
		</div>
	</div>

	<br /><br /><br />

{else}
	<div class="panel panel-default">
		<div class="panel-body">
			{ll key="checkin_label_no_results"}
		</div>
	</div>
{/if}
