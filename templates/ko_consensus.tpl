<button type="button" id="consensus_filter" class="btn btn-primary" style="float:right;">
	<i class="fa fa-filter"></i> {ll key="rota_consensus_filter_title"} <i class="fa fa-caret-down"></i>
</button>
<div id="filter-popover">
	{$tpl_filter}
</div>
<h2>{$tpl_person_name}</h2>
<h3>{$tpl_timespan}</h3>

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
			<tr>
				<th></th>
				{foreach from=$tpl_teams item=team}
					<th colspan="2">
						<div class="team_header">
							{$team.name}
						</div>
					</th>
				{/foreach}
			</tr>
			<tr>
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
					<tr class="even {$row[0].event_status}" data-filter-group="{$row[0].event_groupid}" data-filter-status="{$row[0].consensus_status}">
						{else}
					<tr class="odd {$row[0].event_status}" data-filter-group="{$row[0].event_groupid}" data-filter-status="{$row[0].consensus_status}">
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

<div class="notification notification_warning" style="display:{if $tpl_consensus_message != null}block{else}none{/if};">
	{$tpl_consensus_message}
</div>
