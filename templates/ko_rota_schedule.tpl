<h3>
	<span class="pull-left">
		{$title}
		&nbsp;
		{$help.link}
	</span>


	<div class="pagestats rota-header pull-right">
		<div class="btn-toolbar">

			{if $access_send}
				<div class="btn-group btn-group-sm">
					<a class="btn btn-default" href="index.php?action=show_filesend&filetype=all">
						<i class="fa fa-envelope"></i>
					</a>
				</div>
			{/if}

			{if $access_export}
				<div class="btn-group btn-group-sm">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-file-pdf-o"></i>&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;<span class="caret"></span>
					</button>
					<ul class="dropdown-menu dropdown-menu-right" role="menu">
						{foreach from=$exports item=export}
							<li>
								<a class="export_item" href="javascript:sendReq('../rota/inc/ajax.php', 'action,mode,sesid', 'export,{$export.mode},{$sesid}', show_box);" title="{$export.label}">
									{if $export.icon}
										<img src="{$export.icon}" alt="XLS">
									{else}
										{$export.html}
									{/if}
									&nbsp;{$export.label}
								</a>
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}

			<div class="input-group input-group-sm">
				<div class="input-group-btn auto-width">
					<a class="btn btn-sm btn-default" onclick="sendReq('../rota/inc/ajax.php', 'action,timespan,sesid', 'settimespan,{$stats.prevts},{$sesid}', do_element);" title="{$label_decrease_timespan}">
						<i class="fa fa-minus"></i>
					</a>
				</div>
				<select name="sel_timespan" class="input-sm" onchange="sendReq('../rota/inc/ajax.php', 'action,timespan,sesid', 'settimespan,'+this.options[this.selectedIndex].value+',{$sesid}', do_element);">
					{foreach from=$timespans.values item=v key=k}
						<option value="{$v}" {if $v == $timespans.selected}selected="selected"{/if}>{$timespans.output.$k}</option>
					{/foreach}
				</select>
				<div class="input-group-btn auto-width">
					<a class="btn btn-sm btn-default" onclick="sendReq('../rota/inc/ajax.php', 'action,timespan,sesid', 'settimespan,{$stats.nextts},{$sesid}', do_element);" title="{$label_increase_timespan}">
						<i class="fa fa-plus"></i>
					</a>
				</div>
			</div>



			<div class="btn-group btn-group-sm">
				<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,sesid', 'timeminus,{$sesid}', do_element);">
					<i class="fa fa-angle-left"></i>
				</a>

				<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,sesid', 'timetoday,{$sesid}', do_element);">
					<i class="fa fa-stop"></i>
				</a>

				<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,sesid', 'timeplus,{$sesid}', do_element);">
					<i class="fa fa-angle-right"></i>
				</a>
			</div>

			<div class="btn-group btn-group-sm" style="width:32px;">
				<input type="text" name="rota_dateselect" id="rota_dateselect-input" class="input-sm form-control" value="{$input.avalue}" style="visibility:hidden;width:1px;padding:0px;margin:0px;" onchange="">
				<button type="button" id="rota_dateselect-button" class="btn btn-default" style="position:absolute;top:0px;left:0px;"><i class="fa fa-calendar"></i></button>
				<script>
					$('#rota_dateselect-input').datetimepicker({ldelim}
						locale: kOOL.language,
						format: "YYYY-MM-DD",
						showTodayButton: true,
						useCurrent: false
					{rdelim});
					$('#rota_dateselect-button').click(function(){ldelim}
						$('#rota_dateselect-input').data("DateTimePicker").toggle();
					{rdelim});
					$('#rota_dateselect-input').datetimepicker().on('dp.change', function(e){ldelim}
						sendReq('../rota/inc/ajax.php', 'action,date,sesid', 'settime,'+$('#rota_dateselect-input').val()+','+kOOL.sid, do_element);
					{rdelim})
				</script>
			</div>


			<div class="btn-group btn-group-sm">
				<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,sesid', '{$action_date_future},{$sesid}', do_element);">
					<img src="{$ko_path}images/{$icon_date_future}" border="0" alt="{$label_date_future}" title="{$label_date_future}" />
				</a>
			</div>

			{if $access_status}
				<div class="btn-group btn-group-sm">
					<a class="btn btn-default btn-success" href="javascript:sendReq('../rota/inc/ajax.php', 'action,status,sesid', 'setalleventstatus,1,{$sesid}', do_element);" title="{$label_status_all_open}">
						<i class="fa fa-unlock"></i>
					</a>
					&nbsp;&nbsp;
					<a class="btn btn-default btn-danger" href="javascript:sendReq('../rota/inc/ajax.php', 'action,status,sesid', 'setalleventstatus,2,{$sesid}', do_element);" title="{$label_status_all_close}">
						<i class="fa fa-lock"></i>
					</a>
				</div>
			{/if}


		</div>
	</div>

</h3>

<br clear="all" />



{if $show_weeks}
	{foreach from=$weeks item=week}
		<div class="panel panel-default rota-event" name="rota_week_{$week.id}">
			<div class="panel-heading" style="padding: 5px 15px;">
				<h4 class="panel-title">
					<div class="row">
						<div class="col-sm-6">
							<a style="padding: 6px 0px; display:block;" data-toggle="collapse" href="#rota_week_{$week.id}_collapse" >
								{$label_week}&nbsp;{$week.num}-{$week.year}
							</a>
						</div>
						<div class="col-sm-6">
							<div class="btn-toolbar pull-right">

								<div class="btn-group btn-group-sm">
									<button class="btn btn-default" disabled>{$week._date}</button>
								</div>

								<div class="btn-group btn-group-sm" name="rota_stats_{$week.id}">
									{if $week._stats.done == $week._stats.total}
										{assign var="class" value="success"}
									{elseif $week._stats.done == 0}
										{assign var="class" value="danger"}
									{else}
										{assign var="class" value="warning"}
									{/if}

									<button class="btn btn-{$class}" disabled>{$week._stats.done}/{$week._stats.total}</button>
								</div>

								<div class="btn-group btn-group-sm">
									{if $week.rotastatus == 1}
										<button class="btn btn-success" title="{$label_status_w_opened}" disabled>
											<i class="fa fa-unlock"></i>
										</button>
									{elseif $access_status}
										<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'setweekstatus,{$week.id},1,{$sesid}', do_element);" title="{$label_status_w_open}">
											<i class="fa fa-unlock"></i>
										</a>
									{/if}
									{if $week.rotastatus == 2}
										<button class="btn btn-danger" title="{$label_status_w_closed}" disabled>
											<i class="fa fa-lock"></i>
										</button>
									{elseif $access_status}
										<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'setweekstatus,{$week.id},2,{$sesid}', do_element);" title="{$label_status_w_close}">
											<i class="fa fa-lock"></i>
										</a>
									{/if}
								</div>

							</div>
						</div>
					</div>

				</h4>
			</div>

			<div id="rota_week_{$week.id}_collapse" class="panel-collapse collapse in">
				<div class="panel-body rota-event-content" id="rota_event_content_{$week.id}" style="padding:0px 0px 0px 0px;">

					<div name="rota_schedule_{$week.id}" id="rota_schedule_{$week.id}">
						{$week.schedulling_code}
					</div>

				</div>

			</div>
		</div>
	{/foreach}
{/if}




{foreach from=$events item=event}
	<a id="c{$event.id}"></a>

	<div class="panel panel-default rota-event" name="rota_event_{$event.id}">
		<div class="panel-heading" style="border-bottom-color:#{$event.eventgruppen_farbe}; padding: 5px 15px;">
			<h4 class="panel-title">
				<div class="row">
					<div class="col-sm-6">
						<a style="padding: 6px 0px; display:block;" data-toggle="collapse" href="#rota_event_{$event.id}_collapse" >
							{$event._date}
							&nbsp;
							<b>{$event.eventgruppen_name}</b>
						</a>
					</div>
					<div class="col-sm-6">
						<div class="btn-toolbar pull-right">

							<div class="btn-group btn-group-sm">
								{foreach from=$event.exports item=export}
									<a class="btn btn-default" href="{$export.link}" title="{$export.title}">
										{if $export.img}
											<img src="{$export.img}" border="0">
										{else}
											{$export.html}
										{/if}
									</a>
								{/foreach}
							</div>

							{if $event.room || $event._time}
								<div class="btn-group btn-group-sm">
									{if $event.room}<button class="btn btn-default" disabled>{$event.room}</button>{/if}
									{if $event._time}<button class="btn btn-default" disabled>{$event._time}</button>{/if}
								</div>
							{/if}

							{if $event._stats.done == $event._stats.total}
								{assign var="class" value="success"}
							{elseif $event._stats.done == 0}
								{assign var="class" value="danger"}
							{else}
								{assign var="class" value="warning"}
							{/if}
							<div name="rota_stats_{$event.id}" class="btn-group btn-group-sm">
								<button class="btn btn-{$class}" disabled>{$event._stats.done}/{$event._stats.total}</button>
							</div>


							<div class="btn-group btn-group-sm">
								{if $event.rotastatus == 1}
									<button class="btn btn-success" title="{$label_status_w_opened}" disabled>
										<i class="fa fa-unlock"></i>
									</button>
								{elseif $access_status}
									<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'seteventstatus,{$event.id},1,{$sesid}', do_element);" title="{$label_status_w_open}">
										<i class="fa fa-unlock"></i>
									</a>
								{/if}
								{if $event.rotastatus == 2}
									<button class="btn btn-danger" title="{$label_status_w_closed}" disabled>
										<i class="fa fa-lock"></i>
									</button>
								{elseif $access_status}
									<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'seteventstatus,{$event.id},2,{$sesid}', do_element);" title="{$label_status_w_close}">
										<i class="fa fa-lock"></i>
									</a>
								{/if}
							</div>
						</div>
					</div>
				</div>

			</h4>

		</div>


		<div id="rota_event_{$event.id}_collapse" class="panel-collapse collapse in">
			<ul class="list-group" id="rota_event_content_{$event.id}">
					{foreach from=$show_eventfields item=field}
						{if $event.$field != ''}
							<li class="list-group-item list-group-item-info rota-event-kommentar">
								{$eventfield_labels.$field}: {$event._processed.$field}
							</li>
						{/if}
					{/foreach}
					<li class="list-group-item list-group-item-default" style="padding:0px 0px 0px 0px;">
						<div name="rota_schedule_{$event.id}" id="rota_schedule_{$event.id}">
							{$event.schedulling_code}
						</div>
					</li>

			</ul>

		</div>

	</div>

{/foreach}


<br clear="all" />
<script>$(".selectpicker").selectpicker();</script>


