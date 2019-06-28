{include file="ko_fm_header.tpl"}


<input type="hidden" name="aa_id" id="aa_id" value="" />

{if $tpl_aa_empty}
	{$label_empty}
{else}
	{foreach name=mutationen item=mutation from=$tpl_mutationen}
		<fieldset>
		<legend>{$mutation.name}</legend>

		{if $mutation.family != null}
			<div class="notification notification_info" style="margin-bottom:10px;">{ll key="leute_labels_mutations_is_in_family"}: {$mutation.family.id}. {ll key="leute_warning_mutations_family"}.</div>
		{/if}

		<table>
			{if $mutation.family != null}
				<tr>
					<td>{ll key="family"}:</td>
					<td>{$mutation.family.id}</td>
					<td>&nbsp;</td>
					<td><input type="checkbox" id="chk_{$mutation.id}[decouple_from_family]" name="chk_{$mutation.id}[decouple_from_family]" /></td>
					<td><label for="chk_{$mutation.id}[decouple_from_family]">{ll key="leute_labels_mutations_decouple_from_family"}</label></td>
				</tr>
			{/if}
		{foreach item=field from=$mutation.fields}
			<tr><td>
				{if $mutation.family != null && $field.isFamilyField}<img src="{$ko_path}images/icon_familie.png" title="{ll key="leute_labels_mutations_family_title"}" /> {/if}{$field.desc}:
			</td><td>
				{$field.oldvalue}
			</td><td>
				&nbsp;&rarr;&nbsp;
			</td><td>
				<input type="checkbox" name="chk_{$mutation.id}[{$field.name}]"
				{if !$field.readonly && ($field.newvalue != "" && $field.newvalue != "0000-00-00")}
					checked
				{/if}
				{if $field.readonly}disabled="disabled"{/if}
				/>
			</td><td>
				{if $field.type == 'select'}
					<select name="txt_{$mutation.id}[{$field.name}]" size="0" {if $field.readonly}disabled="disabled"{/if}>
					{foreach from=$field.values item=v key=k}
						<option value="{$v}" {if $v == $field.newvalue}selected="selected"{/if}>{if $field.descs.$k}{$field.descs.$k}{else}{$field.descs.$v}{/if}</option>
					{/foreach}
					</select>
				{else}
					<input type="text" name="txt_{$mutation.id}[{$field.name}]" value="{$field.newvalue}" {if $field.readonly}disabled="disabled"{/if} />
				{/if}
			</td></tr>
		{/foreach}
		<tr><td>&nbsp;</td>
		<td colspan="3">{$label_crdate}:</td>
		<td>{$mutation.crdate} {$mutation.cruserid}</td></tr>
		</table>

		{if $mutation.bemerkung != ""}
			<br /><b>{$label_comments}:</b><br />
			{$mutation.bemerkung}
			<br />
		{/if}

		<p align="center">
		<input type="submit" name="submit_mutation" value="{$label_submit}" onclick="set_action('submit_mutation', this);set_hidden_value('aa_id', '{$mutation.id}', this);this.submit" style="font-weight: bold;" />
		<input type="submit" name="submit_del_mutation" value="{$label_delete}" onclick="set_action('submit_del_mutation', this);set_hidden_value('aa_id', '{$mutation.id}', this);this.submit" />
		</p>

		</fieldset>
	{/foreach}
{/if}


{include file="ko_fm_footer.tpl"}
