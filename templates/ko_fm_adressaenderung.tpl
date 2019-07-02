{if $tpl_aa_show == "name"}
	<form action="index.php?action=show_adressaenderung_fields" method="post">
{/if}
{if $tpl_aa_show == "fields"}
	<form action="index.php?action=submit_aa" method="post">
{/if}

{include file="ko_fm_header.tpl"}


{if $tpl_aa_show == "name"}
	{$label_name}:<br />
	<input type="text" name="txt_fm_aa_nachname" size="14" />
	<br />{$label_firstname}:<br />
	<input type="text" name="txt_fm_aa_vorname" size="14" />
	<br />
	<p align="center">
	<input type="submit" name="submit_fm_aa" value="{$label_ok}" />
	</p>
{/if}


{if $tpl_aa_show == "list"}
	{if $tpl_aa_info}
		<b>{$tpl_aa_info}</b><br /><br />
	{/if}
	<table>
	{foreach from=$tpl_aa_list item=i}
		<tr><td>
		<a href="index.php?action=show_adressaenderung_fields&amp;aa_id={$i.id}&amp;aa_nachname={$i.nachname}&amp;aa_vorname={$i.vorname}">{$i.vorname} {$i.nachname}
		{if $i.id==-1}
			 ({$tpl_label_new})
		{/if}
		</a>
		</td><td>
		{$i.adresse}
		</td></tr>
	{/foreach}
	</table>
{/if}



{if $tpl_aa_show == "fields"}
	<input type="hidden" name="aa_id" id="aa_id" value="{$tpl_aa_id}" />
	{if $tpl_aa_info}
		<b>{$tpl_aa_info}</b><br />
	{/if}
	{if $tpl_aa_id > 0}
		{$title_edit}
	{else}
		{$title_new}
	{/if}
	<br /><br />
	<table border="0">
	{foreach name=settings item=setting from=$tpl_input}
		<tr>
		<td align="right"><b>{$setting.desc}</b></td>
		<td>

		{if $setting.type == "text"}
			<input type="text" size="20" name="{$setting.name}" value="{$setting.value}" {$setting.params} />

		{elseif $setting.type == "select"}
			<select name="{$setting.name}" {$setting.params}>
			{foreach from=$setting.values item=v key=k}
				<option value="{$v}" {if $v == $setting.value}selected="selected"{/if}>{if $setting.descs.$k}{$setting.descs.$k}{else}{$setting.descs.$v}{/if}</option>
			{/foreach}
		 </select>
		{/if}

		</td></tr>
	{/foreach}

	<tr><td align="right"><b>{$label_comment}:</b></td>
	<td><textarea name="txt_bemerkung" cols="40" rows="5"></textarea></td></tr>

	<tr><td colspan="2" align="center">
	<br />
	<input type="submit" name="submit_aa" value="{$label_ok}" />
	&nbsp;&nbsp;
	<input type="submit" name="cancel_aa" value="{$label_cancel}" />
	</td></tr>

	</table>
{/if}

{include file="ko_fm_footer.tpl"}

</form>
