<div class="list-title">{$title}</div>

<div class="list-help">
	{$help.link}
</div>


{if $access_status}
	<div class="rota-statusall">
		<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,status,sesid', 'setalleventstatus,1,{$sesid}', do_element);">
			<img src="{$ko_path}images/statuso.png" alt="O" border="0" title="{$label_status_all_open}" />
		</a>
		&nbsp;&nbsp;
		<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,status,sesid', 'setalleventstatus,2,{$sesid}', do_element);">
			<img src="{$ko_path}images/statusc.png" alt="X" border="0" title="{$label_status_all_close}" />
		</a>
	</div>
{/if}

<div align="right" style="float:right;" class="rota-header">

	{if $access_send}
		<div style="float: left; margin: 2px 12px 0 0;">
			<a href="index.php?action=show_filesend&filetype=all">
				<img src="../images/icon_email.png" border="0" />
			</a>
		</div>
	{/if}

	{if $access_export}
		<div id="export_button">
			<div id="export_flyout">
				{foreach from=$exports item=export}
					<a class="export_item" href="javascript:sendReq('../rota/inc/ajax.php', 'action,mode,sesid', 'export,{$export.mode},{$sesid}', show_box);">
						<img src="{$export.icon}" border="0" alt="XLS" />
						{$export.label}
					</a>
				{/foreach}
			</div>
		</div>
	{/if}



	&nbsp;&nbsp;

	<img src="{$ko_path}/images/decrease.png" border="0" alt="-" style="cursor: pointer;" onclick="sendReq('../rota/inc/ajax.php', 'action,timespan,sesid', 'settimespan,{$stats.prevts},{$sesid}', do_element);" title="{$label_decrease_timespan}" />
	<select name="sel_timespan" size="0" onchange="sendReq('../rota/inc/ajax.php', 'action,timespan,sesid', 'settimespan,'+this.options[this.selectedIndex].value+',{$sesid}', do_element);" style="margin: 0px 5px;">
	{foreach from=$timespans.values item=v key=k}
		<option value="{$v}" {if $v == $timespans.selected}selected="selected"{/if}>{$timespans.output.$k}</option>
	{/foreach}
	</select>
	<img src="{$ko_path}/images/increase.png" border="0" alt="+" style="cursor: pointer;" onclick="sendReq('../rota/inc/ajax.php', 'action,timespan,sesid', 'settimespan,{$stats.nextts},{$sesid}', do_element);" title="{$label_increase_timespan}" />

	&nbsp;&nbsp;&nbsp;


	<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,sesid', 'timeminus,{$sesid}', do_element);">
	<img src="{$ko_path}images/icon_arrow_left.png" border="0" alt="{$label_list_back}" title="{$label_list_back}" />
	</a>

	<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,sesid', 'timetoday,{$sesid}', do_element);">
	<img src="{$ko_path}images/icon_today.png" border="0"  />
	</a>

	<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,sesid', 'timeplus,{$sesid}', do_element);">
	<img src="{$ko_path}images/icon_arrow_right.png" border="0" alt="{$label_list_next}" title="{$label_list_next}"  />
	</a>

	<input name="rota_dateselect" onchange="sendReq('../rota/inc/ajax.php', 'action,date,sesid', 'settime,'+this.value+',{$sesid}', do_element);" size="1" style="display: none;" id="f-calendar-field-1" type="text" />
	<a href="#" id="f-calendar-trigger-1"><img align="middle" border="0" class="jsdate-image" src="../images/calendar.gif" alt="" /></a>
	<script type="text/javascript">
		rota_init_jsdate();
	</script>

	&nbsp;

	<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,sesid', '{$action_date_future},{$sesid}', do_element);">
	<img src="{$ko_path}images/{$icon_date_future}" border="0" alt="{$label_date_future}" title="{$label_date_future}" />
	</a>

	&nbsp;&nbsp;&nbsp;

</div>

<br clear="all" />



{if $show_weeks}
	{foreach from=$weeks item=week}
		<div class="rota-event" name="rota_week_{$week.id}">
			<div class="rota-week-header">
				<div class="rota-header-block">
					{if $week.rotastatus == 1}
						<img src="{$ko_path}images/statuso.png" alt="O" title="{$label_status_w_opened}" />
					{elseif $access_status}
						<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'setweekstatus,{$week.id},1,{$sesid}', do_element);">
							<img src="{$ko_path}images/statusod.png" onmouseover="this.src='{$ko_path}images/statuso.png';" onmouseout="this.src='{$ko_path}images/statusod.png';" alt="O" border="0" title="{$label_status_w_open}" />
						</a>
					{/if}

					&nbsp;&nbsp;

					{if $week.rotastatus == 2}
						<img src="{$ko_path}images/statusc.png" alt="X" title="{$label_status_w_closed}" />
					{elseif $access_status}
						<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'setweekstatus,{$week.id},2,{$sesid}', do_element);">
							<img src="{$ko_path}images/statuscd.png" onmouseover="this.src='{$ko_path}images/statusc.png';" onmouseout="this.src='{$ko_path}images/statuscd.png';" alt="X" border="0" title="{$label_status_w_close}" />
						</a>
					{/if}
				</div>


				{if $week._stats.done == $week._stats.total}
					{assign var="class" value="rota-stats-done"}
				{elseif $week._stats.done == 0}
					{assign var="class" value="rota-stats-empty"}
				{else}
					{assign var="class" value="rota-stats-half"}
				{/if}
				<div name="rota_stats_{$week.id}" class="rota-header-block"><div class="{$class}">{$week._stats.done}/{$week._stats.total}</div></div>

				<div class="rota-header-block">{$week._date}</div>

				<div class="rota-event-header-title" onclick="change_vis('rota_event_content_{$week.id}');">
					<b>{$label_week}&nbsp;{$week.num}-{$week.year}</b>
				</div>

			</div>


			<div class="rota-event-content" id="rota_event_content_{$week.id}">

				<div name="rota_schedule_{$week.id}" id="rota_schedule_{$week.id}">
					{$week.schedulling_code}
				</div>

			</div>

		</div>
	{/foreach}
{/if}




{foreach from=$events item=event}
	<a id="c{$event.id}"></a>

	<div class="rota-event" name="rota_event_{$event.id}">
		<div class="rota-event-header" style="border-bottom-color: #{$event.eventgruppen_farbe};">
			<div class="rota-header-block">
				{if $event.rotastatus == 1}
					<img src="{$ko_path}images/statuso.png" alt="O" title="{$label_status_e_opened}" />
				{elseif $access_status}
					<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'seteventstatus,{$event.id},1,{$sesid}', do_element);">
						<img src="{$ko_path}images/statusod.png" onmouseover="this.src='{$ko_path}images/statuso.png';" onmouseout="this.src='{$ko_path}images/statusod.png';" alt="O" border="0" title="{$label_status_e_open}" />
					</a>
				{/if}

				&nbsp;&nbsp;

				{if $event.rotastatus == 2}
					<img src="{$ko_path}images/statusc.png" alt="X" title="{$label_status_e_closed}" />
				{elseif $access_status}
					<a href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'seteventstatus,{$event.id},2,{$sesid}', do_element);">
						<img src="{$ko_path}images/statuscd.png" onmouseover="this.src='{$ko_path}images/statusc.png';" onmouseout="this.src='{$ko_path}images/statuscd.png';" alt="X" border="0" title="{$label_status_e_close}" />
					</a>
				{/if}
			</div>

			{if $event._stats.done == $event._stats.total}
				{assign var="class" value="rota-stats-done"}
			{elseif $event._stats.done == 0}
				{assign var="class" value="rota-stats-empty"}
			{else}
				{assign var="class" value="rota-stats-half"}
			{/if}
			<div name="rota_stats_{$event.id}" class="rota-header-block"><div class="{$class}">{$event._stats.done}/{$event._stats.total}</div></div>

			<div class="rota-header-block">{$event.room}</div>

			<div class="rota-header-block">{$event._time}</div>

			{foreach from=$event.exports item=export}
				<div class="rota-header-block">
					<a href="{$export.link}">
						<img src="{$export.img}" border="0" title="{$export.title}" />
					</a>
				</div>
			{/foreach}

			<div class="rota-event-header-title" onclick="change_vis('rota_event_content_{$event.id}');">
				{$event._date}
				&nbsp;
				<b>{$event.eventgruppen_name}</b>
			</div>

		</div>


		<div class="rota-event-content" id="rota_event_content_{$event.id}">

			{foreach from=$show_eventfields item=field}
				{if $event.$field != ''}
					<div class="rota-event-kommentar">
						{$eventfield_labels.$field}: {$event._processed.$field}
					</div>
				{/if}
			{/foreach}

			<div name="rota_schedule_{$event.id}" id="rota_schedule_{$event.id}">
				{$event.schedulling_code}
			</div>

		</div>

	</div>

{/foreach}


<br clear="all" />


