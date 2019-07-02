<div style="padding-left:4px;font-size: 1.2em;font-weight:bold;float:left;">
	{$tracking.name}
	&nbsp;&nbsp;<a href="index.php?action=enter_tracking&amp;id={$tracking.id}"><img src="{$ko_path}images/icon_reload.png" border="0" /></a>
	{if $help.show}&nbsp;&nbsp;{$help.link}{/if}
</div>

<br clear="all" />
<div class="list_subtitle">{$tracking.description|regex_replace:"/\n/":"<br />"}</div>

<div style="float: right; white-space: nowrap">
	<img src="{$ko_path}/images/decrease.png" border="0" alt="-" style="margin-bottom: -3px; cursor: pointer;" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', 'setdatelimit,{$limitM},{$sesid}', do_element);" />
	<img src="{$ko_path}/images/increase.png" border="0" alt="-" style="margin-bottom: -3px; cursor: pointer;" onclick="sendReq('../{$module}/inc/ajax.php', 'action,set_limit,sesid', 'setdatelimit,{$limitP},{$sesid}', do_element);" />
	&nbsp;&nbsp;

	<select name="sel_date" size="0" onchange="jumpToUrl('?action=setdate&date='+this.options[this.selectedIndex].value);" style="margin: 0 12px 2px 0;">
	{foreach from=$dateselect.months item=month}
		<option value="{$month.value}" {if $month.value == $dateselect.selected}selected="selected"{/if} {if $month.value == $dateselect.today}style="background-color: #c0c0c0;"{/if}>{$month.desc}</option>
	{/foreach}
	</select>

	{if $prev_date}
		<a href="index.php?action=setdate&date={$prev_date}"><img src="{$ko_path}images/icon_doublearrow_left.png" border="0" title="{$label_prev}" /></a>
	{else}
		<img src="{$ko_path}images/icon_doublearrow_left_disabled.png" border="0" title="{$label_prev}" />
	{/if}

	{if $prev1_date}
		<a href="index.php?action=setdate&date={$prev1_date}"><img src="{$ko_path}images/icon_arrow_left.png" border="0" title="{$label_prev1}" /></a>
	{else}
		<img src="{$ko_path}images/icon_arrow_left_disabled.png" border="0" title="{$label_prev1}" />
	{/if}

	<a href="index.php?action=setdate&date={$today_date}"><img src="{$ko_path}images/icon_today.png" border="0" title="{$label_today}" /></a>

	{if $next1_date}
		<a href="index.php?action=setdate&date={$next1_date}"><img src="{$ko_path}images/icon_arrow_right.png" border="0" title="{$label_next1}" /></a>
	{else}
		<img src="{$ko_path}images/icon_arrow_right_disabled.png" border="0" title="{$label_next1}" />
	{/if}

	{if $next_date}
		<a href="index.php?action=setdate&date={$next_date}"><img src="{$ko_path}images/icon_doublearrow_right.png" border="0" title="{$label_next}" /></a>
	{else}
		<img src="{$ko_path}images/icon_doublearrow_right_disabled.png" border="0" title="{$label_next}" />
	{/if}
</div>

<table width="100%" class="tracking" cellpadding="0" cellspacing="0">
<tr>
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
		</th>
	{/foreach}
	<th class="ko_list">&sum; {$label_sum}</th>
</tr>




<!-- Row to set default values for all visible dates -->
{if $tracking.mode == 'simple' || $tracking.mode == 'value' || $tracking.mode == 'valueNonNum' || $tracking.mode == 'typecheck'}
	<tr class="tracking_preset">
		<td><img src="{$ko_path}images/default.png" border="0" />&nbsp;{$label_preset}</td>

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
			<td>
				{if $tracking.mode == 'simple'}
					<div style="float: left; width: 16px; height: 16px;" id="tstate_-1_{$date.date}"></div>
					{if $readonly}
						{if $value}X{/if}
					{else}
						<input type="checkbox" value="{$value}" id="chk_-1_{$date.date}" {if $value}checked="checked"{/if} onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal} action: 'settrackingsimple', lid: '-1', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, sesid: '{$sesid}' {literal}}{/literal}, tracking_entered_simple);" />
					{/if}

				{elseif $tracking.mode == 'value' || $tracking.mode == 'valueNonNum'}

				{elseif $tracking.mode == 'type'}

				{elseif $tracking.mode == 'typecheck'}
					{foreach from=$types item=t}
						{assign var="v" value=$t.value}
						{assign var="pvalue" value=$value.$v}
						<input type="checkbox" value="1" id="chk_-1_{$date.date}_{$t.value}" {if $pvalue}checked="checked"{/if} onclick="sendReq('../{$module}/inc/ajax.php', 'action,lid,tid,date,type,value,sesid', 'settrackingtypecheck,-1,{$tracking.id},{$date.date},{$t.value},{if $pvalue}0{else}1{/if},{$sesid}', do_element);" />
						<label for="chk_-1_{$date.date}_{$t.value}" {if $pvalue}class="bold"{/if}>{$t.value}</label>
						<br />
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
					<div style="float: left; width: 18px; height: 16px;"></div>
					<img src="{$ko_path}/images/icon_checked.gif" border="0" class="tracking_set_simple_for_all" id="forall_{$tracking.id}_{$date.date}" title="{$label_for_all}" />
					&nbsp;
					<img src="{$ko_path}/images/button_delete.gif" border="0" class="tracking_del_for_all" id="delforall_{$tracking.id}_{$date.date}" title="{$label_for_all_del}" />



				{elseif $tracking.mode == 'value' || $tracking.mode == 'valueNonNum'}
					<input type="text" title="{$label_for_all}" size="10" id="tinput_all_{$tracking.id}_{$date.date}" class="tracking_set_value_for_all" style="float: left;" />
					&nbsp;
					<img src="{$ko_path}/images/button_delete.gif" border="0" class="tracking_del_for_all" id="delforall_{$tracking.id}_{$date.date}" title="{$label_for_all_del}" />


				{elseif $tracking.mode == 'typecheck'}
					{foreach from=$types item=t}
						<img src="{$ko_path}/images/icon_checked.gif" border="0" id="typecheck_forall_{$tracking.id}_{$date.date}_{$t.value}" class="tracking_set_typecheck_for_all cursor_pointer" />
						<span class="tracking_set_typecheck_for_all cursor_pointer" id="typechecklabel_forall_{$tracking.id}_{$date.date}_{$t.value}">{$t.value}</span>
						&nbsp;
						<img src="{$ko_path}/images/button_delete.gif" border="0" class="tracking_del_for_all" id="delforall_{$tracking.id}_{$date.date}_{$t.value}" title="{$label_for_all_del}" />
						<br />
					{/foreach}
				{/if}

			</td>
		{/foreach}
		<td></td>
	</tr>
{/if}




{if $tracking.label_value && ($tracking.mode == 'value' || $tracking.mode == 'type')}
	<tr class="ko_list_even"><th class="ko_list">&nbsp;</td>
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

<tr class="{cycle values="ko_list_odd,ko_list_even"}">
	<td>
		<a name="tp{$p.id}"></a>
		{if $p._inactive}({/if}
		{if $p.vorname != '' || $p.nachname != ''}
			{$p.vorname} {$p.nachname}
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
			<img src="{$ko_path}images/default.png" border="0" class="tracking_default" id="tracking_default_{$tracking.id}_{$pid}" title="{$label_set_default}" />
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
			<td style="white-space: nowrap;" class="tracking_{$tracking.mode}" title="{$timestamp|date_format:'%A, %d.%m.%Y'}: {$p.vorname} {$p.nachname}">
				<div style="float: left; width: 16px; height: 16px;" id="tstate_{$p.id}_{$date.date}"></div>
				{if $readonly}
					{if $value}X{/if}
				{else}
					<input type="checkbox" tabindex="{$tabindex}" value="{$value}" id="chk_{$p.id}_{$date.date}" {if $value}checked="checked"{/if} onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal} action: 'settrackingsimple', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, sesid: '{$sesid}' {literal}}{/literal}, tracking_entered_simple);" />
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
						<input type="checkbox" tabindex="{$tabindex}" value="{$t.value}" id="chk_{$p.id}_{$date.date}_{$t.value}" {if $pvalue}checked="checked"{/if} onclick="sendReq('../{$module}/inc/ajax.php', 'action,lid,tid,date,type,value,sesid', 'settrackingtypecheck,{$p.id},{$tracking.id},{$date.date},{$t.value},{if $pvalue}0{else}1{/if},{$sesid}', do_element);" />
						<label for="chk_{$p.id}_{$date.date}_{$t.value}" {if $pvalue}class="bold"{/if}>{$t.value}</label>
					{/if}
					<br />
				{/foreach}
			</td>


		{elseif $tracking.mode == 'value' || $tracking.mode == 'valueNonNum'}

			{assign var="value" value=$entries.$pid.$d}
			<td style="white-space: nowrap;" class="tracking_{$tracking.mode}" title="{$timestamp|date_format:'%A, %d.%m.%Y'}: {$p.vorname} {$p.nachname}">
				{if $readonly}
					{if $value}{$value}{/if}
				{else}
					<input type="text" tabindex="{$tabindex}" size="10" id="tinput_{$p.id}_{$date.date}" value="{$value}" style="float: left;" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) {literal}{{/literal} $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingvalue', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value); $('#tinput_{$p._next_id}_{$date.date}').focus(); return false;{literal}}{/literal} else return true;" />
					<div style="float: left; width: 16px; height: 16px;">
						<a href="#" onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingvalue', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: $('#tinput_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value); $('#tinput_{$p._next_id}_{$date.date}').focus();" tabindex="90000">
						<img src="{$ko_path}/images/icon_save.gif" width="16" height="16" alt="save" border="0" tabindex="90000" />
						</a>
					</div>
					<div style="float: left; width: 16px; height: 16px;" id="tstate_{$p.id}_{$date.date}"></div>
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
								<img src="{$ko_path}images/icon_trash.png" style="float: left;" border="0" width="16" height="16" alt="X" />
								</a>
								<span {if $show_comments && !$v.comment}onclick="TINY.box.show({literal}{{/literal}url:'../{$module}/inc/ajax.php?action=comment&tid={$tracking.id}&eid={$v.id}&sesid={$sesid}'{literal}}{/literal});" title="{$label_add_comment}"{/if} />
									{$v.type}: {$v.value}
								</span>

								{if $v.status == 1}
									<a href="#" onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'confirmtrackingtype', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', type: '{$v.type}', sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000" title="{$label_confirm_entry}">
									<img src="{$ko_path}images/button_check.png" style="float: right;" border="0" width="16" height="16" alt="OK" />
									</a>
								{/if}

								{if $show_comments && $v.comment}
									<img src="{$ko_path}images/comment.png" style="cursor: pointer;" onclick="TINY.box.show({literal}{{/literal}url:'../{$module}/inc/ajax.php?action=comment&tid={$tracking.id}&eid={$v.id}&sesid={$sesid}'{literal}}{/literal});" onmouseover="tooltip.show('{$v.comment}');" onmouseout="tooltip.hide();" />
								{/if}
							</div>
						{/foreach}
					{/if}
					<a href="#" id="addlink_{$p.id}_{$date.date}" style="float: left; clear: both;">
						<img src="{$ko_path}images/icon_plus.png" border="0" width="16" height="16" alt="+" />
					</a>
					<div id="adddiv_{$p.id}_{$date.date}" style="display: none;">
						<select name="sel_{$p.id}_{$date.date}" id="sel_{$p.id}_{$date.date}" size="0" style="width: 100px; float: left;">
						{foreach from=$types item=t}
							<option value="{$t.value}">{$t.desc}</option>
						{/foreach}
						</select>
						<input type="text" style="width:25px; float: left;" id="tinput_{$p.id}_{$date.date}" value="{$value}" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) {literal}{{/literal} $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingtype', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: this.value, type: $('#sel_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type); return false;{literal}}{/literal} else return true;" />
						<div style="float: left; width: 16px; height: 16px;">
							<a href="#" onclick="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingtype', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', type: $('#sel_{$p.id}_{$date.date}').val(), value: $('#tinput_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000">
							<img src="{$ko_path}/images/icon_save.gif" width="16" height="16" alt="save" border="0" />
							</a>
						</div>
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
							<img src="{$ko_path}images/icon_trash.png" style="float: left; cursor: pointer;" border="0" width="16" height="16" alt="X" title="{$v}" onclick="c=confirm('{$label_del_confirm}'); if(!c) return false; $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'deltrackingbitmask', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: '{$k}', sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000" />

							<span class="tracking_entry_{$tracking.mode}_{$k}">{$v}</span>

							<br clear="all" />
						{/foreach}
					{/if}

					{if $show_comments && $comments.$pid.$d}
						<img src="{$ko_path}images/icon_trash.png" style="float: left; cursor:pointer;" border="0" width="16" height="16" alt="X" title="{$label_del_comment}" onclick="c=confirm('{$label_del_confirm}'); if(!c) return false; $.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'delcomment', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type);" tabindex="90000" />

						<img src="{$ko_path}images/comment.png" style="cursor: pointer;" onclick="TINY.box.show({literal}{{/literal}url:'../{$module}/inc/ajax.php?action=comment&tid={$tracking.id}&lid={$p.id}&date={$date.date}&sesid={$sesid}'{literal}}{/literal});" onmouseover="tooltip.show('{$comments.$pid.$d}');" onmouseout="tooltip.hide();" />
						<br clear="all" />
					{/if}

					<img src="{$ko_path}images/icon_plus.png" class="tracking_{$tracking.mode}_add" border="0" width="16" height="16" alt="+" id="addlink_{$p.id}_{$date.date}" style="float: left; cursor: pointer; display: none;" />
					<div id="adddiv_{$p.id}_{$date.date}" style="display: none;">
						<select name="sel_{$p.id}_{$date.date}" id="sel_{$p.id}_{$date.date}" size="0" onchange="$.get('../{$module}/inc/ajax.php', {literal}{{/literal}action: 'settrackingbitmask', lid: '{$p.id}', tid: '{$tracking.id}', date: '{$date.date}', value: $('#sel_{$p.id}_{$date.date}').val(), sesid: '{$sesid}'{literal}}{/literal}, tracking_entered_value_type); return false;">
						<option value=""></option>
						{foreach from=$types item=t}
							<option value="{$t.value}" title="{$t.desc}">{$t.desc}</option>
						{/foreach}
						</select>
						{if $show_comments && $values != "" && $comments.$pid.$d == ""}
							<img src="{$ko_path}images/comment.png" style="cursor: pointer;" onclick="TINY.box.show({literal}{{/literal}url:'../{$module}/inc/ajax.php?action=comment&tid={$tracking.id}&lid={$p.id}&date={$date.date}&sesid={$sesid}'{literal}}{/literal});" title="{$label_add_comment}" />
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
