{if $warning}<div class="errortxt">{$warning}</div>{/if}

<table width="100%" cellspacing="0"><tr><td class="subpart_header">
{$tpl_cal_titel}
</td><td align="left">
&nbsp;
<a href="index.php?action=show_cal_jahr"><img src="{$ko_path}images/cal_jahr.gif" border="0" alt="{$label_cal_year}" title="{$label_cal_year}"></a>
&nbsp;
<a href="index.php?action=show_cal_monat"><img src="{$ko_path}images/cal_month.gif" border="0" alt="{$label_cal_month}" title="{$label_cal_month}"></a>
&nbsp;
<a href="index.php?action=show_cal_woche"><img src="{$ko_path}images/cal_week.gif" border="0" alt="{$label_cal_week}" title="{$label_cal_week}"></a>
</td><td align="right">
<a href="{$tpl_prev_link}">
<img src="{$ko_path}images/icon_arrow_left.png" border="0" alt="back" title="back" /></a>&nbsp;
<a href="{$tpl_today_link}">
<img src="{$ko_path}images/icon_today.png" border="0" alt="today" title="{$label_today}" /></a>&nbsp;
<a href="{$tpl_next_link}">
<img src="{$ko_path}images/icon_arrow_right.png" border="0" alt="next" title="next" /></a>
</td></tr>

<tr><td class="subpart" colspan="3">

<table width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #999999; empty-cells:show; border-collapse:collapse;">
<tr>
<td class="kalender_header" align="center"width="10%"><b>{$label_item}</b></td>
<td width="1px" style="border:0px;">&nbsp;</td>
{foreach from=$tpl_cal_month item=month}
	<td class="kalender_header" align="center"width="{$tpl_cal_month_width}%" onclick="jumpToUrl('?action=show_cal_monat&amp;set_month={$month.code}');"><b>{$month.name}</b></td>
	<td width="1px" style="border:0px;">&nbsp;</td>
{/foreach}
</tr>

{literal}
<script language="javascript" type="text/javascript">
<!--
function pointer(row) {
	aktColor = row.getAttribute('bgcolor');

	if(aktColor == "#cccccc") newColor = "#ffffff";
	else newColor = "#cccccc";

	row.setAttribute('bgcolor', newColor, 0);
	return true;
}
-->
</script>
{/literal}


{foreach from=$tpl_day item=days}
	<tr onclick="pointer(this);">
	<td width="10%" onmouseover="tooltip.show('{$days.tip}');" onmouseout="tooltip.hide();"><b>{$days.name|truncate:18:"...":false}</b></td>
	<td width="1px" style="border:0px;">&nbsp;</td>
	{foreach from=$days.events item=monat}
		<td width="{$tpl_cal_month_width}%">
		<table width="100%" border="1" style="border:1px solid #999999; empty-cells:show; border-collapse:collapse;">
		<tr>
		{foreach from=$monat.days item=day}
			<td height="20px" width="3%" style="{$day.style}" {if $day.tip != ""}onmouseover="tooltip.show('{$day.tip}','','t','c');" onmouseout="tooltip.hide();"{/if}></td>
		{/foreach}
		</tr>
		</table>
		</td>

		<td width="1px" style="border:0px;">&nbsp;</td>
	{/foreach}
	</tr>
{/foreach}
</table>


</td>
</tr>
</table>


{if $show_list_footer}
	<table style="margin-left:12px" cellspacing="0" cellpadding="3">
	<tr><td style="border-left-style:solid;border-left-width:1px">&nbsp;</td></tr>
	
	{foreach from=$list_footer item=footer}
		<tr><td style="border-left-style:solid;border-left-width:1px;border-bottom-width:1px;border-bottom-style:solid;">
		&nbsp;{$footer.label}
		&nbsp;&nbsp;{$footer.button}
		</td></tr>
	{/foreach}

	</table>
{/if}
