<button type="button" id="consensus_filter" class="btn btn-primary" style="float:right;">
	<i class="fa fa-filter"></i> {ll key="rota_consensus_filter_title"} <i class="fa fa-caret-down"></i>
</button>

<div id="filter-popover">
    {$tpl_filter}
</div>

<h2>{$tpl_person_name}</h2>
<h3>{$tpl_timespan}</h3>

{if $tpl_ongoing_cal}
<div id="ongoing_calendar">
	<div class="btn-group btn-group-sm">
		<a class="btn btn-default" href="{$tpl_ongoing_cal.urls.left}"><i class="fa fa-angle-left"></i></a>
		<a class="btn btn-default" href="{$tpl_ongoing_cal.urls.today}"><i class="fa fa-stop"></i></a>
		<a class="btn btn-default" href="{$tpl_ongoing_cal.urls.right}"><i class="fa fa-angle-right"></i></a>
	</div>

	<div class="btn-group btn-group-sm" style="width:32px;">
		<input type="text" name="consensus_dateselect" id="consensus_dateselect-input" class="input-sm form-control" value="">
		<button type="button" id="consensus_dateselect-button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
		<script>
			$('#consensus_dateselect-input').datetimepicker({ldelim}
				locale: "{$tpl_language}",
				format: "YYYY-MM-DD",
				showTodayButton: true,
				useCurrent: false
			{rdelim});
			$('#consensus_dateselect-button').click(function(){ldelim}
				$('#consensus_dateselect-input').data("DateTimePicker").toggle();
			{rdelim});
			$('#consensus_dateselect-input').datetimepicker().on('dp.change', function(e){ldelim}
				window.location.href = "{$tpl_ongoing_cal.urls.cal}" + $('#consensus_dateselect-input').val();
			{rdelim})
		</script>
	</div>
</div>
{/if}

<br />
{if $tpl_description_needed == true}
	<table id="consensus_descriptions">
		<tr>
			<th>{ll key="ko_consensus_descriptions_title"}</th>
			<td></td>
		</tr>
		{if $tpl_general_description != ''}
			<tr>
				<th>{ll key="ko_consensus_descriptions_general"}</th>
				<td>{$tpl_general_description|nl2br}</td>
			</tr>
		{/if}
		{foreach from=$tpl_teams item=team}
			{if $team.consensus_description != ''}
				<tr>
					<th>{$team.name}</th>
					<td>{$team.consensus_description|nl2br}</td>
				</tr>
			{/if}
		{/foreach}
	</table>
{/if}
<div id="consensus_entries_wrapper">
	<table id="consensus_entries">
		{if ($tpl_events|@count > 0)}
			<tr data-type="header">
				<th></th>
				{foreach from=$tpl_teams item=team}
					<th colspan="2">
						<div class="team_header">
							{$team.name}
						</div>
					</th>
				{/foreach}
			</tr>
			<tr data-type="header">
				<th></th>
				{foreach from=$tpl_teams item=team}
					<th colspan="2">
						<div class="team_header">
							<button class="btn btn-default btn-xs" data-action="comment_dialog" data-team_id="{$team.id}" data-comment_text="{$team.consensus_comment}">
								{if $team.consensus_comment}<i class="fa fa-comment"></i>{else}<i class="fa fa-comment-o"></i>{/if} {ll key="ko_consensus_comment"}
							</button>
						</div>
					</th>
				{/foreach}
			</tr>

			{counter name=rowCounter start=0 skip=1 assign=rowCount print=false}
			{foreach from=$tpl_data item=row}
				{if $rowCount is div by 2}
					<tr class="even {$row[0].event_status}" id="event_{$row[0].event_id}" data-filter-group="{$row[0].event_groupid}" data-filter-status="{$row[0].consensus_status}">
						{else}
					<tr class="odd {$row[0].event_status}"  id="event_{$row[0].event_id}" data-filter-group="{$row[0].event_groupid}" data-filter-status="{$row[0].consensus_status}">
				{/if}
				{counter name="columnCounter" start=0 skip=1 assign=columnCount print=false}
				{foreach from=$row item=cell}
					{if ($columnCount == 0)}
						{$cell.content}
					{else}
						{if $cell.type == "empty"}
							{$cell.content}
						{else}
							{$cell.content.person}{$cell.content.team}
						{/if}

					{/if}
					{counter name="columnCounter" print=false}
				{/foreach}
				</tr>
				{counter name="rowCounter" print=false}
			{/foreach}
		{/if}
	</table>


	{if ($tpl_weeks|@count > 0)}
	<table id="consensus_entries_amtstage">
		{foreach from=$tpl_weeks key=week_id item=week}
			<tr data-type="header" class="header">
				<th class="week_label">{$week.label}</th>
				<th>
					<ol class="btn-group daysrange">
						{foreach from=$week.days key=day_id item=day}
							<li class="btn btn-default rota-tooltip" data-tooltip-code="{$day}">{ll key="kota_ko_rota_teams_days_range_values[$day_id]"}</li>
                        {/foreach}
					</ol>
				</th>
			</tr>

			{foreach from=$week.teams key=team_id item=team}
				<tr id="{$team_id}_{$week_id}_list" data-filter-status="{$team.filter_status}">
					<th class="header_team">{$team.details.name}</th>
					<th>{$team.input}</th>
				</tr>
			{/foreach}
		{/foreach}
	</table>
	{/if}
</div>

<div class="modal fade" id="comment_modal" role="dialog" aria-labelledby="comment_modal_label" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="comment_modal_label">{ll key="ko_consensus_comment"}</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<input type="hidden" name="comment_team_id" id="comment_team_id" />
					<textarea class="form-control" rows="5" name="comment_text" id="comment_text"></textarea>
				</div>
				<button type="button" data-action="comment_save" class="btn btn-primary">{ll key="save"}</button>
			</div>
		</div>
	</div>
</div>

<div class="notification notification_warning_no_result" style="display:{if $tpl_consensus_message_no_result != null}block{else}none{/if};">
	{$tpl_consensus_message_no_result}
</div>

<div class="notification notification_warning_not_allowed" style="display:{if $tpl_consensus_message_not_allowed != null}block{else}none{/if};">
    {$tpl_consensus_message_not_allowed}
</div>
