{if $tpl_aa_show == "name"}
	<form action="index.php?action=show_adressaenderung_fields" method="post">
{/if}
{if $tpl_aa_show == "fields"}
	<form action="index.php?action=submit_aa" method="post">
{/if}

{include file="ko_fm_header.tpl"}


{if $tpl_aa_show == "name"}
	<label>{$label_name}:</label>
	<input class="input-sm form-control" type="text" name="txt_fm_aa_nachname">
	<label>{$label_firstname}:</label>
	<input class="input-sm form-control" type="text" name="txt_fm_aa_vorname">
	<div class="btn-field">
		<button class="btn btn-sm btn-primary" type="submit" name="submit_fm_aa" value="{$label_ok}">
			{$label_ok}
		</button>
	</div>
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
	<div class="form-horizontal">
	{foreach name=settings item=setting from=$tpl_input}
			<div class="form-group">

				<label class="col-sm-4 control-label">{$setting.desc}</label>

				<div class="col-sm-8">
					{if $setting.type == "text"}
						<input class="input-sm form-control" type="text" name="{$setting.name}" value="{$setting.value}" {$setting.params}>
					{elseif $setting.type == "select"}
						<select class="input-sm form-control" name="{$setting.name}" {$setting.params}>
							{foreach from=$setting.values item=v key=k}
								<option value="{$v}" {if $v == $setting.value}selected="selected"{/if}>{if $setting.descs.$k}{$setting.descs.$k}{else}{$setting.descs.$v}{/if}</option>
							{/foreach}
						</select>
					{/if}
				</div>

			</div>
		{/foreach}


		<div class="row form-group">
			<label class="col-sm-4">{$label_comment}:</label>
			<div class="col-sm-8">
				<textarea class="input-sm form-control" name="txt_bemerkung" cols="40" rows="5"></textarea>
			</div>
		</div>
		</div>

	<div class="btn-field">
	<button class="btn btn-sm btn-primary" type="submit" name="submit_aa" value="{$label_ok}">
		{$label_ok}
	</button>
	<button class="btn btn-sm btn-danger" type="submit" name="cancel_aa" value="{$label_cancel}">
		{$label_cancel}
	</button>
	</div>
{/if}

{include file="ko_fm_footer.tpl"}

</form>
