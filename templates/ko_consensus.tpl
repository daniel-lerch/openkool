<h2>{$tpl_person_name}: {$tpl_timespan}</h2>
{if $tpl_description_needed == true}
	<table id="consensus_descriptions">
		<tr>
			<th>{ll key="ko_consensus_descriptions_title"}</th>
			<th></th>
		</tr>
		{if $tpl_general_description != ''}
			<tr>
				<td>{ll key="ko_consensus_descriptions_general"}</td>
				<td>{$tpl_general_description}</td>
			</tr>
		{/if}
		{foreach from=$tpl_teams item=team}
			{if $team.consensus_description != ''}
				<tr>
					<td>{$team.name}</td>
					<td>{$team.consensus_description}</td>
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
				{foreach from=$tpl_events item=event}
					<th colspan="2">
						<div class="event_header {if $event.status == 1}open{else}closed{/if}">
							<h3>{$event.startingDate}<br />{$event.startingTime}</h3>
							<div style="cursor:pointer;" onmouseover="tooltip.show('{$event._processed.eventgruppen_name}');" onmouseout="tooltip.hide();" class="event_comment">{$event._processed.eventgruppen_name}</div>
							{foreach from=$tpl_show_eventfields item=field}
								{if $event.$field != ''}
									<div style="cursor:pointer;" onmouseover="tooltip.show('{$event._processed.$field}');" onmouseout="tooltip.hide();" class="event_comment">
										{$tpl_eventfield_labels.$field}: {$event._processed.$field}
									</div>
								{/if}
							{/foreach}
						</div>
					</th>
				{/foreach}
			</tr>
			{counter name=rowCounter start=0 skip=1 assign=rowCount print=false}
			{foreach from=$tpl_data item=row}
				{if $rowCount is div by 2}
					<tr class="even">
						{else}
					<tr class="odd">
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
{if $tpl_consensus_message != null}
	<div class="notification notification_warning">
		{$tpl_consensus_message}
	</div>
{/if}