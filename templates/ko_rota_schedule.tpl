
{include file="ko_rota_stats.tpl"}

	{foreach from=$weeks item=week}
		{if $show_days && $week.id}
		<div class="panel panel-default rota-event" name="rota_day_{$day.id}">
			<div class="panel-heading" style="padding: 5px 15px;">
				<h4 class="panel-title">
					<div class="row">
						<div class="col-sm-6">
							<a style="padding: 6px 0px; display:block;" data-toggle="collapse" href="#rota_day_{$week.id}_collapse" >
								{$week.label}
							</a>
						</div>
						<div class="col-sm-6">
							<div class="btn-toolbar pull-right">
								<div class="btn-group btn-group-sm">
								{if $week._stats.done >= $week._stats.total}
									{assign var="class" value="success"}
								{elseif $week._stats.done == 0}
									{assign var="class" value="danger"}
								{else}
									{assign var="class" value="warning"}
								{/if}
								<div name="rota_stats_{$week.id}" class="btn-group btn-group-sm">
									<button class="btn btn-{$class}" disabled>{$week._stats.done}/{$week._stats.total}</button>
								</div>

								<div class="btn-group btn-group-sm">
									{foreach from=$week.exports item=export}
										<a class="btn btn-default" href="{$export.link}" title="{$export.title}">
											{if $export.img}
												<img src="{$export.img}" border="0">
											{else}
												{$export.html}
											{/if}
										</a>
									{/foreach}
								</div>

								<div class="btn-group btn-group-sm">
									{if $week.rotastatus == 1}
										<button class="btn btn-success" title="{$label_status_w_opened}" disabled>
											<i class="fa fa-unlock"></i>
										</button>
									{elseif $access_status}
										<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'seteventstatus,{$week.id},1,{$sesid}', do_element);" title="{$label_status_w_open}">
											<i class="fa fa-unlock"></i>
										</a>
									{/if}
									{if $week.rotastatus == 2}
										<button class="btn btn-danger" title="{$label_status_w_closed}" disabled>
											<i class="fa fa-lock"></i>
										</button>
									{elseif $access_status}
										<a class="btn btn-default" href="javascript:sendReq('../rota/inc/ajax.php', 'action,id,status,sesid', 'seteventstatus,{$week.id},2,{$sesid}', do_element);" title="{$label_status_w_close}">
											<i class="fa fa-lock"></i>
										</a>
									{/if}
								</div>
							</div>
						</div>
					</div>

				</h4>
			</div>


			<div id="rota_day_{$week.id}_collapse" class="panel-collapse collapse in">
				<div class="panel-body rota-event-content" id="rota_event_content_{$week.id}" style="padding:0px 0px 0px 0px;">
					<div name="rota_schedule_{$week.id}" id="rota_schedule_{$week.id}">
						{$week.schedulling_code}
					</div>
				</div>
			</div>

		</div>
		{/if}


        {foreach from=$week.events item=event}
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

                                    {if $event.can_edit}
										<div class="btn-group btn-group-sm">
											<a class="btn btn-default" href="/daten/index.php?action=edit_termin&id={$event.id}" title="{ll key="daten_edit_event"}">
												<i class="fa fa-pencil"></i>
											</a>
											<a class="btn btn-default" href="/daten/index.php?action=delete_termin&id={$event.id}&returnAction=rota:schedule" title="{ll key="daten_delete_event"}" onclick="javascript:c=confirm('{ll key="daten_delete_event_confirm"}'); if(!c) {literal}{{/literal}return false;{literal}}{/literal}">
												<i class="fa fa-remove"></i>
											</a>
										</div>
                                    {/if}

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
                                            {if $event.room}<button class="btn btn-default" disabled>{$event.room.title}</button>{/if}
                                            {if $event._time}<button class="btn btn-default" disabled>{$event._time}</button>{/if}
										</div>
                                    {/if}

                                    {if $event._stats.done >= $event._stats.total}
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


				<div id="rota_event_{$event.id}_collapse" class="panel-collapse collapse in" style="will-change:transform;">
					<ul class="list-group" id="rota_event_content_{$event.id}">
                        {foreach from=$show_eventfields item=field}
                            {if $event._processed.$field != ''}
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
	{/foreach}

<br clear="all" />
<script>{literal}$(".selectpicker").selectpicker({lazyLoadLiElements:true});{/literal}</script>


