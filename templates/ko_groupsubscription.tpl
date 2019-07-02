{include file="ko_fm_header.tpl"}


<input type="hidden" name="gid" id="gid" value="" />
<input type="hidden" name="_id" id="_id" value="" />
<input type="hidden" name="lid" id="lid" value="" />

{if $tpl_list_empty}
	{$label_no_entries}
{else}

	<span style="font-weight: bold;">&nbsp;{$label_filter}:&nbsp;</span>
	<select name="sel_gid_filter" size="0" id="gs_filter">
		<option value="">{$label_all} ({$gid_counter_total})</option>
		{foreach item=group from=$gids key=gid}
			<option  value="{$gid}" {if $gid == $current_filter}selected="selected"{/if}>{$group.name} ({$gid_counter.$gid})</option>
		{/foreach}
	</select>
	<br /><br />


	{foreach item=p from=$tpl_gs}
		<fieldset>
		<legend>
			{$label_new_groupsubscription} <b>{$p.groupname}</b>
			{if $p.ezmlm}&nbsp;<sup>{$p.ezmlm}</sup>{/if}
			{if $p.group_limit}&nbsp;({$p.group_limit}){/if}
		</legend>

		<div class="formular_header" style="width: 100%;">{$label_entered_data}:</div>
		<table>
		<tr class="formular_content">
			<td style="padding: 0px 5px 0px 5px;">

			{if !$hide_roles}
				<div class="gs_role_select">
					<label>{$label_role}:</label><br />
					<select name="gs_role_{$p._id}" size="0">
						{$p._role_options}
					</select>
				</div>
			{/if}

			{if $p.firm != ''}{$p.firm}<br />{/if}
			{if $p.name != ' '}{$p.name}<br />{/if}
			{if $p.address != ''}{$p.address}<br />{/if}
			{if $p.plz != '' || $p.ort != ''}{$p.plz} {$p.ort}<br />{/if}
			{if $p.telp != ''}{$p.telp}<br />{/if}
			{if $p.email != ''}{$p.email}<br />{/if}
			{if $p.geburtsdatum != ''}{$p.geburtsdatum} ({$p._age})<br />{/if}
			</td>
			<td style="padding: 0px 5px 0px 5px;">
			{$p.datafields}
			</td>
			<td style="padding: 0px 5px 0px 5px;">
			{$p._bemerkung}
			</td>
		</tr>
		<tr><td colspan="3">
			<em>{$label_crdate}: {$p._crdate}</em>
		</td></tr>
		</table>
		<br />

		{if $p.group_full}
			<b>{$label_group_full}</b>
			<p>
				<input type="submit" name="submit_gs_delete" value="{$label_delete_entry}" onclick="c = confirm('{$label_delete_entry_confirm}'); if(!c) return false; set_action('submit_gs_delete', this);set_hidden_value('_id', '{$p._id}', this);this.submit" />
			</p>
		{else}
			<table>
			<div class="formular_header" style="width: 100%;">{$label_possible_db_hits}:</div>
			{if $p.empty}
				{$label_no_person_in_db}
			{/if}
			{foreach item=dbp from=$p.db}
				<tr><td>
				- {if $dbp.firm}{$dbp.firm}{/if} {if $dbp.department}({$dbp.department}){/if} {$dbp.name}
				</td><td>
				<input type="submit" name="submit" value="{$label_ok}" onclick="set_action('submit_gs', this);set_hidden_value('_id', '{$dbp._id}', this);set_hidden_value('gid', '{$dbp.gid}', this);set_hidden_value('lid', '{$dbp.lid}', this);this.submit" />
				&nbsp;&nbsp;
				<input type="submit" name="submit" value="{$label_ok_and_mutation}" onclick="set_action('submit_gs_aa', this);set_hidden_value('_id', '{$dbp._id}', this);set_hidden_value('gid', '{$dbp.gid}', this);set_hidden_value('lid', '{$dbp.lid}', this);this.submit" />
				</td></tr>
				<tr><td colspan="2">
				&nbsp;&nbsp;{$dbp.adressdaten} 
				</td><td>
			{/foreach}

			<tr><td>
				<div>-&nbsp;{$label_ps}</div>
				<table><tr><td>&nbsp;&nbsp;</td><td valign="top">
					<div class="peoplesearchwrap">
						<input type="text" class="peoplesearch" name="txt_{$p.ps.name}" autocomplete="off" /><br />
						<select class="peoplesearchresult" size="2" name="sel_ds1_{$p.ps.name}">
					</div>
				</td><td valign="top">
					<img src="{$ko_path}images/ds_del.gif" alt="del" title="{$label_doubleselect_remove}" border="0" onclick="double_select_move('{$p.ps.name}', 'del');"/>
				</td><td valign="top">
					<input type="hidden" name="{$p.ps.name}" value="" />
					<select name="sel_ds2_{$p.ps.name}" size="5" class="peoplesearchact"> </select>
				</td>
				<td valign="bottom">
					<input type="submit" name="submit" value="{$label_ok}" onclick="set_action('submit_gs_ps', this);set_hidden_value('_id', '{$p._id}', this);set_hidden_value('gid', '{$p.ps.gid}', this);this.submit" />
					&nbsp;&nbsp;
					<input type="submit" name="submit" value="{$label_ok_and_mutation}" onclick="set_action('submit_gs_ps_aa', this);set_hidden_value('_id', '{$p._id}', this);set_hidden_value('gid', '{$p.ps.gid}', this);this.submit" />
				</td></tr></table>
			</td></tr>

			</table>

			<div>
			<p>{$label_add_person}</p>
			<input type="submit" name="submit_gs_new_person" value="{$label_add_person_submit}" onclick="set_action('submit_gs_new_person', this);set_hidden_value('_id', '{$p._id}', this);set_hidden_value('gid', '{$p.gid}', this);this.submit" />
			&nbsp;&nbsp;
			<input type="submit" name="submit_gs_delete" value="{$label_delete_entry}" onclick="c = confirm('{$label_delete_entry_confirm}'); if(!c) return false; set_action('submit_gs_delete', this);set_hidden_value('_id', '{$p._id}', this);this.submit" />
			</div>
		{/if}

		</fieldset>
		<br />
	{/foreach}
{/if}


{include file="ko_fm_footer.tpl"}
