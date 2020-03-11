<h3 class="clearfix">
	<span class="pull-left">
		{$tracking.name}
		&nbsp;&nbsp;<a href="index.php?action=select_tracking&amp;id={$tracking.id}"><i class="fa fa-refresh"></i></a>
		{if $help.show}
			&nbsp;&nbsp;{$help.link}
		{/if}
	</span>

	<small style="position: relative; top: 7px; margin-left: 22px;">{$subtitle}</small>

	<div class="pagestats pull-right">
		<div class="btn-toolbar">
			<div class="btn-group btn-group-sm">
				<button type="button" class="btn btn-default" alt="-" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', 'setdatelimit,{$limitM},{$sesid}', do_element);"><i class="fa fa-minus"></i></button>
				<button type="button" class="btn btn-default" alt="-" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', 'setdatelimit,{$limitP},{$sesid}', do_element);"><i class="fa fa-plus"></i></button>
			</div>

			<div class="input-group input-group-sm">
				<select class="input-sm form-control" name="sel_date" size="0" onchange="jumpToUrl('?action=setdate&date='+this.options[this.selectedIndex].value);" style="margin: 0 12px 2px 0;">
					{foreach from=$dateselect.months item=month}
						<option value="{$month.value}" {if $month.value == $dateselect.selected}selected="selected"{/if} {if $month.value == $dateselect.today}style="background-color: #c0c0c0;"{/if}>{$month.desc}</option>
					{/foreach}
				</select>
				</select>
			</div>

			<div class="btn-group btn-group-sm">
			{if $prev_date}
				<a class="btn btn-default" href="index.php?action=setdate&date={$prev_date}" title="{$label_prev}"><i class="fa fa-angle-double-left"></i></a>
			{else}
				<button class="btn btn-default" title="{$label_prev}" disabled><i class="fa fa-angle-double-left"></i></button>
			{/if}

			{if $prev1_date}
				<a class="btn btn-default" href="index.php?action=setdate&date={$prev1_date}" title="{$label_prev1}"><i class="fa fa-angle-left"></i></a>
			{else}
				<button class="btn btn-default" title="{$label_prev1}" disabled><i class="fa fa-angle-left"></i></button>
			{/if}

			<a class="btn btn-default" href="index.php?action=setdate&date={$today_date}" title="{$label_today}"><i class="fa fa-play"></i></a>

			{if $next1_date}
				<a class="btn btn-default" href="index.php?action=setdate&date={$next1_date}" title="{$label_next1}"><i class="fa fa-angle-right"></i></a>
			{else}
				<button class="btn btn-default" title="{$label_next1}" disabled><i class="fa fa-angle-right"></i></button>
			{/if}

			{if $next_date}
				<a class="btn btn-default" href="index.php?action=setdate&date={$next_date}" title="{$label_next}"><i class="fa fa-angle-double-right"></i></a>
			{else}
				<button class="btn btn-default" title="{$label_next}" disabled><i class="fa fa-angle-double-right"></i></button>
			{/if}
			</div>
		</div>
	</div>
</h3>


<div class="list_subtitle">
	{$tracking.description|regex_replace:"/\n/":"<br />"}
</div>


<table class="table table-striped table-bordered table-condensed" class="tracking">
<tr class="info">
	<th class="ko_list">{$label_name}</th>

	{foreach from=$show_cols item=col}
		<th class="ko_list">{$show_cols_title.$col}</th>
	{/foreach}

	<!-- set values from preset -->
	{if $tracking.mode == 'simple' || $tracking.mode == 'typecheck'}
		<th class="ko_list">&nbsp;</th>
	{/if}

	{foreach from=$dates item=date}
		<th class="ko_list">
			<a href="index.php?action=setdate&amp;date={$date.date}">{$date.title}</a>
			{if $date.subtitle}<p style="margin: 0;">{$date.subtitle}</p>{/if}
		</th>
	{/foreach}
	<th class="ko_list">&sum; {$label_sum}</th>
</tr>




<!-- Row to set default values for all visible dates -->
{if $tracking.mode == 'simple' || $tracking.mode == 'value' || $tracking.mode == 'valueNonNum' || $tracking.mode == 'typecheck'}
	<tr>
		<td><i class="fa fa-star" style="color:#ffcd00;"></i> {$label_preset}</td>

		{foreach from=$show_cols item=col}
			<td>&nbsp;</td>
		{/foreach}

		<!-- set values from preset -->
		{if $tracking.mode == 'simple' || $tracking.mode == 'typecheck'}
			<td>&nbsp;</td>
		{/if}

		{foreach from=$dates item=date}
			{assign var="d" value=$date.date}
			{assign var="value" value=$preset_values.$d}
			<td class="tracking-status-cell" id="tstate_-1_{$date.date}">
				{if $tracking.mode == 'simple'}
					{if $readonly}
						{if $value}X{/if}
					{else}
						<input type="checkbox" value="{$value}" id="chk_-1_{$date.date}" {if $value}checked="checked"{/if} onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal} action: 'settrackingsimple', lid: '-1', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, sesid: '{$sesid}' {literal}}{/literal}, tracking_entered_simple);">
					{/if}

				{elseif $tracking.mode == 'value' || $tracking.mode == 'valueNonNum'}

				{elseif $tracking.mode == 'type'}

				{elseif $tracking.mode == 'typecheck'}
					{foreach from=$types item=t}
						{assign var="v" value=$t.value}
						{assign var="pvalue" value=$value.$v}
						<div class="checkbox nomargin">
							<label for="chk_-1_{$date.date}_{$t.value}">
								<input type="checkbox" value="1" id="chk_-1_{$date.date}_{$t.value}" {if $pvalue}checked="checked"{/if} onclick="sendReq('../{$module}/inc/ajax.php', 'action,lid,tid,date,type,value,sesid', 'settrackingtypecheck,-1,{$tracking.id},{$date.date},{$t.value},{if $pvalue}0{else}1{/if},{$sesid}', do_element);">
								{if $pvalue}<b>{/if}
									{$t.value}
								{if $pvalue}</b>{/if}
							</label>
						</div>
					{/foreach}
				{/if}
			</td>
		{/foreach}
		<td></td>
	</tr>
{/if}




<!-- Set values for all people on a given date -->
{if ($tracking.mode == 'simple' || $tracking.mode == 'value' || $tracking.mode == 'valueNonNum' || $tracking.mode == 'typecheck') && !$readonly}
	<tr class="tracking_all">
		<td>{$label_for_all}</td>

		{foreach from=$show_cols item=col}
			<td>&nbsp;</td>
		{/foreach}

		<!-- set values from preset -->
		{if $tracking.mode == 'simple' || $tracking.mode == 'typecheck'}
			<td>&nbsp;</td>
		{/if}


		{foreach from=$dates item=date}
			<td>

				{if $tracking.mode == 'simple'}
					<a class="tracking_set_simple_for_all" href="#" id="forall_{$tracking.id}_{$date.date}" title="{$label_for_all}"><i class="fa fa-check-square-o"></i></a>
					&nbsp;
					<a class="tracking_del_for_all text-danger" href="#" id="delforall_{$tracking.id}_{$date.date}" title="{$label_for_all_del}"><i class="fa fa-remove"></i></a>


				{elseif $tracking.mode == 'value' || $tracking.mode == 'valueNonNum'}
					<div class="input-group input-group-sm">
						<input type="text" class="input-sm form-control tracking_set_value_for_all" title="{$label_for_all}" size="10" id="tinput_all_{$tracking.id}_{$date.date}">
						<div class="input-group-btn">
							<button type="button" class="btn btn-default tracking_del_for_all" id="delforall_{$tracking.id}_{$date.date}" title="{$label_for_all_del}"><i class="fa fa-remove"></i></button>
						</div>
					</div>

				{elseif $tracking.mode == 'typecheck'}
					{foreach from=$types item=t}
						<a class="tracking_set_typecheck_for_all cursor_pointer" id="typecheck_forall_{$tracking.id}_{$date.date}_{$t.value}">
							<i class="fa fa-check-square-o"></i>&nbsp;{$t.value}
						</a>
						&nbsp;
						<a class="tracking_del_for_all cursor_pointer" id="delforall_{$tracking.id}_{$date.date}_{$t.value}" title="{$label_for_all_del}">
							<i class="fa fa-remove"></i>
						</a>
						<br>
					{/foreach}
				{/if}

			</td>
		{/foreach}
		<td></td>
	</tr>
{/if}




{if $tracking.label_value && ($tracking.mode == 'value' || $tracking.mode == 'type')}
	<tr class="primary"><th class="ko_list">&nbsp;</td>
	{foreach from=$show_cols item=col}
		<th class="ko_list">&nbsp;</td>
	{/foreach}

	{foreach from=$dates item=date}
		<th class="ko_list"><label>{$tracking.label_value}</label></td>
	{/foreach}
	<th class="ko_list">&nbsp;</td></tr>
{/if}



{assign var="pcounter" value=""}
{foreach from=$people item=p}
	{assign var="pcounter" value=$pcounter+1}
	{assign var="pid" value=$p.id}
	{assign var="tabindex" value=$pcounter}

<tr>
	<td>
		<a name="tp{$p.id}"></a>
		{if $p._inactive}({/if}
		{if $p.vorname != '' || $p.nachname != ''}
			{if $tracking_order_people == 'vorname'}
				{$p.vorname} {$p.nachname}
			{else}
				{$p.nachname} {$p.vorname}
			{/if}
		{else}
			{$p.firm}
		{/if}
		{if $p._inactive}){/if}
	</td>

	{foreach from=$show_cols item=col}
		<td>{$p.$col}</td>
	{/foreach}

	<!-- set values from preset -->
	{if $tracking.mode == 'simple' || $tracking.mode == 'typecheck'}
		<td>
			<a href="#" class="tracking_default" id="tracking_default_{$tracking.id}_{$pid}" title="{$label_set_default}" style="color:#ffcd00;">
				<i class="fa fa-star"></i>
			</a>
		</td>
	{/if}

	{assign var="dcounter" value="-1"}
	{foreach from=$dates item=date}
		{assign var="dcounter" value=$dcounter+1}
		{assign var="d" value=$date.date}
		{assign var="timestamp" value=$date.timestamp}
		{assign var="tabindex" value=$tabindex+$total*$dcounter}


		{if $tracking.mode == 'simple'}

			{assign var="value" value=$entries.$pid.$d}
			<td style="white-space: nowrap;" id="tstate_{$p.id}_{$date.date}" class="tracking_{$tracking.mode} tracking-status-cell" title="{$timestamp|date_format:'%A, %d.%m.%Y'}: {$p.vorname} {$p.nachname}">
				{if $readonly}
					{if $p.value}X{/if}
				{else}
					<input type="checkbox" tabindex="{$tabindex}" value="{$value}" id="chk_{$p.id}_{$date.date}" {if $value}checked="checked"{/if} onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal} action: 'settrackingsimple', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, sesid: '{$sesid}' {literal}}{/literal}, tracking_entered_simple);">
				{/if}
			</td>



		{elseif $tracking.mode == 'typecheck'}

			{assign var="pvalues" value=$entries.$pid.$d}
			<td style="white-space: nowrap;" class="tracking_{$tracking.mode}" title="{$timestamp|date_format:'%A, %d.%m.%Y'}: {$p.vorname} {$p.nachname}">
				{foreach from=$types item=t}

					{assign var="pvalue" value="0"}
					{foreach from=$pvalues item=pv}
						{if $pv.type == $t.value && $pv.value == 1}
							{assign var="pvalue" value="1"}
						{/if}
					{/foreach}

					{if $readonly}
						{$t.value}:&nbsp;
						{if $pvalue}X{/if}
					{else}
						<div class="checkbox nomargin">
							<label>
								<input type="checkbox" tabindex="{$tabindex}" value="{$t.value}" id="chk_{$p.id}_{$date.date}_{$t.value}" {if $pvalue}checked="checked"{/if} onclick="sendReq('../{$module}/inc/ajax.php', 'action,lid,tid,date,type,value,sesid', 'settrackingtypecheck,{$p.id},{$tracking.id},{$date.date},{$t.value},{if $pvalue}0{else}1{/if},{$sesid}', do_element);">
								{if $pvalue}<b>{/if}{$t.value}{if $pvalue}</b>{/if}
							</label>
						</div>
					{/if}
				{/foreach}
			</td>


		{elseif $tracking.mode == 'value' || $tracking.mode == 'valueNonNum'}

			{assign var="value" value=$entries.$pid.$d}
			<td style="white-space: nowrap;" class="tracking_{$tracking.mode} tracking-status-cell" id="tstate_{$p.id}_{$date.date}" title="{$timestamp|date_format:'%A, %d.%m.%Y'}: {$p.vorname} {$p.nachname}">
				{if $readonly}
					{if $value}{$value}{/if}
				{else}
					<div class="input-group input-group-sm">
						<input class="input-sm form-control" type="text" tabindex="{$tabindex}" id="tinput_{$p.id}_{$date.date}" value="{$value}" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) {literal}{{/literal} $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingvalue', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value); $('#tinput_{$p._next_id}_{$date.date}').focus(); return false;{literal}}{/literal} else return true;">
						<div class="input-group-btn">
							<button type="button" class="btn btn-default" alt="save" onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingvalue', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: $('#tinput_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value); $('#tinput_{$p._next_id}_{$date.date}').focus();" tabindex="90000">
								<i class="fa fa-save"></i>
							</button>
						</div>
					</div>
				{/if}
			</td>


		{elseif $tracking.mode == 'type'}
			{assign var="values" value=$entries.$pid.$d}
			<td style="vertical-align: top; width: 170px;" class="tracking_{$tracking.mode}" title="{$timestamp|date_format:'%A, %d.%m.%Y'}: {$p.vorname} {$p.nachname}">
				{if $readonly}
					{if $values}
						{foreach from=$values item=v}
							<div class="tracking-entry-type tracking-entry-readonly {if $v.status == 1}tracking-entry-status1{/if}">
								{$v.type}: {$v.value}
							</div>
						{/foreach}
					{/if}
				{else}
					{if $values}
						{foreach from=$values item=v}
							<div class="tracking-entry-type {if $v.status == 1}tracking-entry-status1{/if}">
								<a href="#" onclick="c=confirm('{$label_del_confirm}'); if(!c) return false; $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'deltrackingtype', id: '{$v.id}', sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000">
									<i class="fa fa-remove"></i>
								</a>
								<span {if $show_comments && !$v.comment}onclick="TINY.box.show({literal}{{/literal}url:'../{$module}/inc/ajax.php?action=comment&tid={$tracking.id}&eid={$v.id}&sesid={$sesid}'{literal}}{/literal});" title="{$label_add_comment}"{/if} />
									{$v.type}: {$v.value}
								</span>

								{if $v.status == 1}
									<a href="#" onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'confirmtrackingtype', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', type: '{$v.type}', sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000" title="{$label_confirm_entry}">
									<i class="fa fa-check"></i>
									</a>
								{/if}

								{if $show_comments && $v.comment}
									<i class="fa fa-comment-o" style="cursor: pointer;" onclick="TINY.box.show({literal}{{/literal}url:'../{$module}/inc/ajax.php?action=comment&tid={$tracking.id}&eid={$v.id}&sesid={$sesid}'{literal}}{/literal});" onmouseover="tooltip.show('{$v.comment}');" onmouseout="tooltip.hide();"></i>
								{/if}
							</div>
						{/foreach}
					{/if}
					<a class="text-success cursor_pointer" id="addlink_{$p.id}_{$date.date}">
						<i class="fa fa-plus"></i>
					</a>
					<br>
					<div id="adddiv_{$p.id}_{$date.date}" style="display: none;">
						<table>
							<tr>
								<td>
									<select name="sel_{$p.id}_{$date.date}" id="sel_{$p.id}_{$date.date}" class="input-sm">
										{foreach from=$types item=t}
											<option value="{$t.value}">{$t.desc}</option>
										{/foreach}
									</select>
								</td>
								<td>
									<input type="text" class="input-sm form-control" style="min-width:80px;" id="tinput_{$p.id}_{$date.date}" value="{$value}" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) {literal}{{/literal} $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingtype', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, type: $('#sel_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type); return false;{literal}}{/literal} else return true;" />
								</td>
								<td>
									<a class="btn btn-primary" onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingtype', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', type: $('#sel_{$p.id}_{$date.date}').val(), value: $('#tinput_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000">
										<i class="fa fa-floppy-o"></i>
									</a>
								</td>
							</tr>
						</table>
					</div>
				{/if}
			</td>



		{elseif $tracking.mode|truncate:8:"" == "bitmask_"}
			{assign var="values" value=$entries.$pid.$d}
			{assign var="num_value" value=$num_entries.$pid.$d}

			{assign var="value" value=0}
			{foreach from=$values key=k item=v}
				{assign var="value" value=$value+$k}
			{/foreach}

			{if $timestamp+(12*3600) > $smarty.now}
				{assign var="timeclass" value="future"}
			{else}
				{assign var="timeclass" value="past"}
			{/if}

			<td style="vertical-align: top;" class="tracking_{$tracking.mode} tracking_{$tracking.mode}_{$timeclass}_{$value}" title="{$timestamp|date_format:'%A, %d.%m.%Y'}: {$p.vorname} {$p.nachname}">
				{if $readonly}
					{if $values}
						{foreach from=$values key=k item=v}
							{$v}<br />
						{/foreach}
					{/if}
				{else}

					{if $values}
						{foreach from=$values key=k item=v}
							<i class="fa fa-times" style="cursor: pointer;" title="{$v}" onclick="c=confirm('{$label_del_confirm}'); if(!c) return false; $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'deltrackingbitmask', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: '{$k}', sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000"></i>

							<span class="tracking_entry_{$tracking.mode}_{$k}">{$v}</span>

							<br clear="all" />
						{/foreach}
					{/if}

					{if $show_comments && $comments.$pid.$d}
						<i class="fa fa-times" style="cursor:pointer;" title="{$label_del_comment}" onclick="c=confirm('{$label_del_confirm}'); if(!c) return false; $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'delcomment', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000"></i>

						<i class="fa fa-comment-o" style="cursor: pointer;" onclick="TINY.box.show({literal}{{/literal}url:'../{$module}/inc/ajax.php?action=comment&tid={$tracking.id}&lid={$p.id}&date={$date.date}&sesid={$sesid}'{literal}}{/literal});" onmouseover="tooltip.show('{$comments.$pid.$d}');" onmouseout="tooltip.hide();"></i>
						<br clear="all" />
					{/if}

					<i class="fa fa-plus tracking_{$tracking.mode}_add" border="0" width="16" height="16" alt="+" id="addlink_{$p.id}_{$date.date}" style="float: left; cursor: pointer; display: none;"></i>
					<div id="adddiv_{$p.id}_{$date.date}" style="display: none;">
						<select class="input-sm form-control" name="sel_{$p.id}_{$date.date}" id="sel_{$p.id}_{$date.date}" size="0" onchange="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingbitmask', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: $('#sel_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type); return false;">
						<option value=""></option>
						{foreach from=$types item=t}
							<option value="{$t.value}" title="{$t.desc}">{$t.desc}</option>
						{/foreach}
						</select>
						{if $show_comments && $values != "" && $comments.$pid.$d == ""}
							<i class="fa fa-comment-o" style="cursor: pointer;" onclick="TINY.box.show({literal}{{/literal}url:'../{$module}/inc/ajax.php?action=comment&tid={$tracking.id}&lid={$p.id}&date={$date.date}&sesid={$sesid}'{literal}}{/literal});" title="{$label_add_comment}"></i>
						{/if}
						<br />
						<input type="text" size="10" id="num_{$p.id}_{$date.date}" value="{$num_value}" style="float: left;" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) {literal}{{/literal} $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingbitmask', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type); return false;{literal}}{/literal} else return true;" />
						<div style="float: left; width: 16px; height: 16px;">
							<img src="{$ko_path}/images/icon_save.gif" style="cursor: pointer;" width="16" height="16" alt="save" border="0" tabindex="90000" onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingbitmask', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: $('#num_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" />
						</div>

					</div>
				{/if}
			</td>

		{/if}


	{/foreach}

	<td>{$p._sum}</td>
</tr>
{/foreach}

<tr>
<th class="ko_list"><b>{$label_total} ({$total})</b></th>

{foreach from=$show_cols item=col}
	<th class="ko_list">&nbsp;</th>
{/foreach}

<!-- set values from preset -->
{if $tracking.mode == 'simple' || $tracking.mode == 'typecheck'}
	<th class="ko_list"></th>
{/if}

{foreach from=$dates item=date}
	{assign var="d" value=$date.date}
	<th class="ko_list">{if $sums.$d}{$sums.$d}{/if}</th>
{/foreach}
<th class="ko_list">&nbsp;</th>
</tr>

</table>
