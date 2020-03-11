{include file="ko_rota_stats.tpl"}

{if count($events) > 0}
<div id="planning_list_wrapper">
<div id="planning_list">
	<div class="header">
		<div class="header-help">
			<span class="glyphicon glyphicon-info-sign"></span>
			<small>{ll key="rota_planning_help"}</small>
		</div>
		<div class="header_team col"></div>
		{foreach from=$events item=event}
			<div class="header_event col col-{$event.id} rota-tooltip" data-tooltip-code='
					{foreach from=$show_eventfields item=field}
						{if $event._processed.$field != ''}
								<h4>{$eventfield_labels.$field}:</h4> {$event._processed.$field}<br />
						{/if}
					{/foreach}
' data-placement="bottom">
				<div>
					{ $event.eventgruppen_name } { $event.startdatum|date_format:"%d.%m.%Y" }<br />
					<span class="event_title">{ $event.title }</span>
				</div>
			</div>
		{/foreach}
		<div class="header_sum col"></div>
	</div>

	{foreach from=$teams item=team}
		<div class="team {if $team.hide}closed{/if}" data-team="{$team.id}">
			<div class="team_name">
				{$team.name}
			</div>

			{foreach from=$events item=event}
				<div class="team_event_info col" name="rota_schedule_event_{$team.id}_{$event.id}_sum">
					{if $team.events[$event.id].sum_scheduled > 0}
						{$team.events[$event.id].sum_scheduled}
					{else}
						{if $access.rota.MAX >= 5}
							{if $team.events[$event.id].status == "disabled"}
								<a class="text-danger" href="javascript:sendReq('../rota/inc/ajax.php', 'action,eventid,teamid,status,type,sesid', 'seteventteamstatus,{$event.id},{$team.id},0,planning,'+kOOL.sid, do_element);update_team_status({$event.id},{$team.id},0);" title="{ll key='rota_status_e_t_open'}"><i class="fa fa-ban"></i></a>
							{elseif $team.events[$event.id].status == "active"}
								<a class="text-hidden text-hover-danger" href="javascript:sendReq('../rota/inc/ajax.php', 'action,eventid,teamid,status,type,sesid', 'seteventteamstatus,{$event.id},{$team.id},1,planning,'+kOOL.sid, do_element);update_team_status({$event.id},{$team.id},1);" title="{ll key='rota_status_e_t_close'}"><i class="fa fa-ban"></i></a>
							{/if}
						{/if}
					{/if}
				</div>
			{/foreach}

			<div class="team_sum col" name="rota_schedule_team_sum_{$team.id}">
				{$team.sum_scheduled} / {$team.sum_events}
			</div>
		</div>

		{if $team.hide != TRUE}
		{foreach from=$team.groups item=member}
			{cycle values='row-odd,row-even' assign=CellCSS}

			<div class="team_member {$CellCSS}" data-team="{$team.id}">
				<div class="member_name col {$CellCSS}" data-member="g{$member.id}">
					[{$member.name}]
				</div>

				{foreach from=$events item=event}
					<div class="member_event_info col col-{$event.id} {$team.edit_class} rota-tooltip
{if $member.consensus[$event.id].status != "active" || $member.consensus[$event.id].status != "active"} consensus-disabled{/if}
 consensus_{$member.consensus[$event.id].answer}" data-event="{$event.id}" data-member="g{$member.id}" data-team="{$team.id}" data-consensus-status="{$member.consensus[$event.id].answer}" data-consensus-scheduled="{$member.consensus[$event.id].scheduled}" {if $member.consensus[$event.id].status != "active"} title="{ll key='rota_consensus_team_disabled'}"{else} title="{$event.startdatum|date_format:"%a, %e. %B %Y"} {$event.startzeit|substr:0:-3} Uhr<br />{$event.eventgruppen_name}: {$event.title}<br />{$member.name}"{/if} name="rota_schedule_{$event.id}_{$team.id}_g{$member.id}">
						<i class="fa"></i>
					</div>
				{/foreach}

				<div class="member_sum col" name="rota_schedule_{$team.id}_g{$member.id}_sum" data-member="{$member.id}" data-team="{$team.id}">
					{$member.consensus.sum.scheduled}
				</div>
			</div>
		{/foreach}

		{foreach from=$team.people item=member}
			{cycle values='row-odd,row-even' assign=CellCSS}

			<div class="team_member {$CellCSS}" data-team="{$team.id}">
				<div class="member_name col {$CellCSS}" data-member="{$member.id}">
					<span class="rota-tooltip" data-tooltip-url="/rota/inc/ajax.php?action=minigraph&amp;sesid={$sesid}&amp;person={$member.id}&amp;team={$team.id}" data-tooltip-width="438" data-tooltip-height="180" data-tooltip-combine-text="true" data-tooltip-show-minigraph="true" data-member="{$member.id}">
						{$member.vorname} {$member.nachname}
					</span>
				</div>

				{foreach from=$events item=event}
					<div class="member_event_info col col-{$event.id} {$team.edit_class}
{if $team.events[$event.id].status != "active" || $member.consensus[$event.id].status != "active"} consensus-disabled{/if}
 consensus_{$member.consensus[$event.id].answer} rota-tooltip{if $team.events[$event.id].status == "active" && $member.consensus[$event.id].status == "active" && isset($member.consensus[$event.id].absence)} member_absent{/if}" data-event="{$event.id}" data-member="{$member.id}" data-team="{$team.id}" data-consensus-status="{$member.consensus[$event.id].answer}" data-consensus-scheduled="{$member.consensus[$event.id].scheduled}" name="rota_schedule_{$event.id}_{$team.id}_{$member.id}"

{if $team.events[$event.id].status != "disabled" && $member.consensus[$event.id].status != "disabled"} title="{$event.startdatum|date_format:"%a, %e. %B %Y"} {$event.startzeit|substr:0:-3} Uhr<br />{$event.eventgruppen_name}: {$event.title}<br />{$member.vorname} {$member.nachname}{if isset($member.consensus[$event.id].absence)}<br />{$member.consensus[$event.id].absence}{/if}"
{elseif $member.consensus[$event.id].status != "active"} title="{ll key='rota_consensus_team_disabled'}"{/if}>
						<i class="fa"></i>
					</div>
				{/foreach}

				<div class="member_sum col" name="rota_schedule_{$team.id}_{$member.id}_sum" data-member="{$member.id}" data-team="{$team.id}">
					{$member.consensus.sum.scheduled}
				</div>
			</div>
		{/foreach}

		{foreach from=$team.free_text item=member key=free_text_id}
			{cycle values='row-odd,row-even' assign=CellCSS}

			<div class="team_member {$CellCSS}" data-team="{$team.id}">
				<div class="member_name col {$CellCSS}" data-member="{$member.name|escape:"quotes"}">
					"{$member.name}"
				</div>

				{foreach from=$events item=event}
					<div class="member_event_info col col-{$event.id} rota-tooltip {$team.edit_class}
{if $team.events[$event.id].status != "active"} consensus-disabled{/if}
" data-event="{$event.id}" data-member="{$member.name|escape:"quotes"}" data-team="{$team.id}" data-consensus-status="{$member.consensus[$event.id].answer}" data-consensus-scheduled="{$member.consensus[$event.id].scheduled}" name="rota_schedule_{$event.id}_{$team.id}_free_{$free_text_id}"
					title="{$event.startdatum|date_format:"%a, %e. %B %Y"} {$event.startzeit|substr:0:-3} Uhr<br />{$event.eventgruppen_name}: {$event.title}<br />{$member.name}" >
						<i class="fa"></i>
					</div>
				{/foreach}

				<div class="member_sum col" name="rota_schedule_{$team.id}_free_{$free_text_id}_sum">
					{$member.consensus.sum.scheduled}
				</div>
			</div>
		{/foreach}

		<div class="team_member" data-team="{$team.id}">
			<div class="member_name col freetext_row"></div>

			{foreach from=$events item=event}
				<div class="member_event_info col col-{$event.id} rota-tooltip
{if $team.events[$event.id].status != "active"} consensus-disabled{/if}
" data-event="{$event.id}" data-member="free_text" data-team="{$team.id}" data-consensus-status="0" data-consensus-scheduled="0">
					<i class="fa fa-user-plus add_freetext_person" title="{ll key='rota_add_freetext_person'}"></i>
				</div>
			{/foreach}

			<div class="member_sum col">
			</div>
		</div>
		{/if}
	{/foreach}
</div>

<script>
	rota_planning_list_init();
</script>

</div>

{else}
	<br />
	<div class="alert alert-warning" role="alert">
	{ll key='rota_no_data_available'}
	</div>
{/if}

<div id="planning_list_mobile_info" class="alert alert-warning" role="alert">
	{ll key="rota_planning_no_mobile"}
</div>
