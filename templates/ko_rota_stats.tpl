<h3>
	<span class="pull-left">
		{$title}
		&nbsp;
		{$help.link}
	</span>


	<div class="pagestats rota-header pull-right">
		<div class="btn-toolbar">

			{if $access_send}
				<div class="input-group input-group-sm">
					<select name="sel_open_consensus_for" class="input-sm">
						<option value="">{ll key="rota_open_consensus_for"}</option>
						{foreach from=$consensus_links item=v key=k}
							<option value="{$k}">{$v}</option>
						{/foreach}
					</select>
					<script>
						$('select[name="sel_open_consensus_for"]').change(function () {ldelim}
							if (!$(this).val()) return;
							window.open($(this).val(), '_blank');
							{rdelim});
					</script>
				</div>
			{/if}

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
					<a class="btn btn-sm btn-default" onclick="sendReq('../rota/inc/ajax.php', 'action,timespan,type,sesid', 'settimespan,{$stats.prevts},{$type},{$sesid}', do_element);" title="{$label_decrease_timespan}">
						<i class="fa fa-minus"></i>
					</a>
				</div>
				<select name="sel_timespan" class="input-sm" onchange="sendReq('../rota/inc/ajax.php', 'action,timespan,type,sesid', 'settimespan,'+this.options[this.selectedIndex].value+',{$type},{$sesid}', do_element);">
					{foreach from=$timespans.values item=v key=k}
						<option value="{$v}" {if $v == $timespans.selected}selected="selected"{/if}>{$timespans.output.$k}</option>
					{/foreach}
				</select>
				<div class="input-group-btn auto-width">
					<a class="btn btn-sm btn-default" onclick="sendReq('../rota/inc/ajax.php', 'action,timespan,type,sesid', 'settimespan,{$stats.nextts},{$type},{$sesid}', do_element);" title="{$label_increase_timespan}">
						<i class="fa fa-plus"></i>
					</a>
				</div>
			</div>



			<div class="btn-group btn-group-sm">
				<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,type,sesid', 'timeminus,{$type},{$sesid}', do_element);">
					<i class="fa fa-angle-left"></i>
				</a>

				<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,type,sesid', 'timetoday,{$type},{$sesid}', do_element);">
					<i class="fa fa-stop"></i>
				</a>

				<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,type,sesid', 'timeplus,{$type},{$sesid}', do_element);">
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
						sendReq('../rota/inc/ajax.php', 'action,date,type,sesid', 'settime,'+$('#rota_dateselect-input').val()+',{$type},'+kOOL.sid, do_element);
						{rdelim})
				</script>
			</div>


			<div class="btn-group btn-group-sm">
				<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,type,sesid', '{$action_date_future},{$type},{$sesid}', do_element);">
					<img src="{$ko_path}images/{$icon_date_future}" border="0" alt="{$label_date_future}" title="{$label_date_future}" />
				</a>
			</div>

			{if $access_status}
				<div class="btn-group btn-group-sm">
					<a class="btn btn-default btn-success" href="javascript:sendReq('../rota/inc/ajax.php', 'action,status,type,sesid', 'setalleventstatus,1,{$type},{$sesid}', do_element);" title="{$label_status_all_open}">
						<i class="fa fa-unlock"></i>
					</a>
					&nbsp;&nbsp;
					<a class="btn btn-default btn-danger" href="javascript:sendReq('../rota/inc/ajax.php', 'action,status,type,sesid', 'setalleventstatus,2,{$type},{$sesid}', do_element);" title="{$label_status_all_close}">
						<i class="fa fa-lock"></i>
					</a>
				</div>
			{/if}
		</div>
	</div>

</h3>

<br clear="all" />
