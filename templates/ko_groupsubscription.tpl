<h3>{$tpl_fm_title}</h3>


<input type="hidden" name="gid" id="gid" value="" />
<input type="hidden" name="_id" id="_id" value="" />
<input type="hidden" name="lid" id="lid" value="" />

	<div class="row">
		<div class="col-sm-6">
			<span style="font-weight: bold;">&nbsp;{$label_filter}:&nbsp;</span>
			<select name="sel_gid_filter" class="input-sm form-control" id="gs_filter">
				<option value="">{$label_all} ({$gid_counter_total})</option>
				{foreach item=group from=$gids key=gid}
					<option  value="{$gid}" {if $gid == $current_filter}selected="selected"{/if}>{$group.name} ({$gid_counter.$gid})</option>
				{/foreach}
			</select>
		</div>
	</div>
	<br>
{if $tpl_list_empty}
	{$label_no_entries}
{else}
	{foreach item=p from=$tpl_gs}
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
					{$label_new_groupsubscription} <b {tooltip text=$p.groupname_full}>{$p.groupname}</b>
					{if $p.ezmlm}&nbsp;<sup>{$p.ezmlm}</sup>{/if}
					{if $p.group_limit}&nbsp;({$p.group_limit}){/if}
				</h4>
			</div>
			<div class="panel-body">
				<div class="list-group col-md-6">
					<div class="list-group-item list-group-item-info">
						{$label_entered_data}
					</div>
					<div class="list-group-item">
						{if !$hide_roles}
							<div class="gs_role_select">
								<label>{$label_role}:</label><br />
								<select name="gs_role_{$p._id}" size="0">
									{$p._role_options}
								</select>
							</div>
						{/if}

						{foreach item=field from=$tpl_gs_fields}
							{if $field == 'vorname'}
								{$p.$field} {$p.nachname}<br />
							{elseif $field == 'plz'}
								{$p.$field} {$p.ort}<br />
							{elseif $field == 'nachname'}
							{elseif $field == 'ort'}
							{elseif $field == 'geburtsdatum'}
								{if $p.$field != '0000-00-00'}
									{$p.$field} {$p.ort} ({$p._age})<br />
								{/if}
							{else}
								{if $p.$field != ''}{$p.$field}<br />{/if}
							{/if}
						{/foreach}
					</div>
					<div class="list-group-item">
						{$p.datafields}
					</div>
					<div class="list-group-item">
						{$p._bemerkung}
					</div>
					{if $p.num_additional_groups > 0}
						<div class="list-group-item">
							<label>{$label_agroups}:</label><br />
							{assign var="height" value=$p.num_additional_groups*20}
							<div class="koi-checkboxes-container" style="height: {$height}px;">
								<input type="hidden" name="gs_agroups_{$p._id}" value="{$p.agroups_avalue}" class="koi-checkboxes-value" />
								<input type="hidden" name="old_gs_agroups_{$p._id}" value="{$p.agroups_avalue}" />
								{foreach from=$p.additional_groups item=ag}
									{if $ag.checked}
										<div class="koi-checkboxes-entry koi-checkboxes-checked">
											<input type="checkbox" name="chk_agroup_{$ag.id}" value="{$ag.id}" checked="checked" />
											<span>{$ag.title}</span>
										</div>
									{else}
										<div class="koi-checkboxes-entry">
											<input type="checkbox" name="chk_agroup_{$ag.id}" value="{$ag.id}" />
											<span>{$ag.title}</span>
										</div>
									{/if}
								{/foreach}
							</div>
						</div>
					{/if}
					<div class="list-group-item">
						<em>{$label_crdate}: {$p._crdate}</em>
					</div>
				</div>
				<div class="list-group col-md-6">
					<div class="list-group-item list-group-item-info">
						{ll key="leute_revisions_assign_fields"}
					</div>

					{if $p.group_full}
						<div class="list-group-item list-group-item-danger">
							<label class="text-danger">{$label_group_full}</label>
							<div class="btn-field">
								<button class="btn btn-sm btn-danger" type="submit" name="submit_gs_delete" value="{$label_delete_entry}" onclick="c = confirm('{$label_delete_entry_confirm}'); if(!c) return false; set_action('submit_gs_delete', this);set_hidden_value('_id', '{$p._id}', this);this.submit">
									{$label_delete_entry}
								</button>
							</div>
						</div>
					{else}
						<div class="list-group-item">
							<label>{$label_possible_db_hits}</label>
							{if $p.empty}
								{$label_no_person_in_db}
							{/if}
							{foreach item=dbp from=$p.db}
								<div style="width:auto;" data-toggle="tooltip" data-html="true" data-container="body" title="{$dbp.adressdaten}">{if $dbp.firm}{$dbp.firm}{/if} {if $dbp.department}({$dbp.department}){/if} {$dbp.name}</div>
								<div class="btn-group btn-group-sm">
									<button type="submit" class="btn btn-sm btn-primary" onclick="set_action('submit_gs', this);set_hidden_value('_id', '{$dbp._id}', this);set_hidden_value('gid', '{$dbp.gid}', this);set_hidden_value('lid', '{$dbp.lid}', this);this.submit">
										{$label_ok}
									</button>
									<button type="submit" class="btn btn-sm btn-primary" onclick="set_action('submit_gs_aa', this);set_hidden_value('_id', '{$dbp._id}', this);set_hidden_value('gid', '{$dbp.gid}', this);set_hidden_value('lid', '{$dbp.lid}', this);this.submit">
										{$label_ok_and_mutation}
									</button>
								</div>
							{/foreach}
						</div>
						<div class="list-group-item">
							<label>{ll key="leute_revisions_list_add_to_selected_person"} / {$label_ps}</label>
							<input type="hidden" name="{$p.ps.name}" id="html-id-{$p.ps.name}">
							<script>
								$('#html-id-{$p.ps.name}').peoplesearch({ldelim}
									multiple: false,
									excludeSql: '`hidden` = 0'
									{rdelim});
							</script>
							<div class="btn-group btn-group-sm" style="margin-top: 5px;">
								<button class="btn btn-sm btn-primary" type="submit" name="submit" value="{$label_ok}" onclick="set_action('submit_gs_ps', this);set_hidden_value('_id', '{$p._id}', this);set_hidden_value('gid', '{$p.ps.gid}', this);this.submit">
									{$label_ok}
								</button>
								<button class="btn btn-sm btn-primary" type="submit" name="submit" value="{$label_ok_and_mutation}" onclick="set_action('submit_gs_ps_aa', this);set_hidden_value('_id', '{$p._id}', this);set_hidden_value('gid', '{$p.ps.gid}', this);this.submit">
									{$label_ok_and_mutation}
								</button>
							</div>
						</div>
						<div class="list-group-item">
							<label>{$label_add_person}</label>
							<div class="btn-group btn-group-sm">
								<button class="btn btn-sm btn-success" type="submit" name="submit_gs_new_person" value="{$label_add_person_submit}" onclick="set_action('submit_gs_new_person', this);set_hidden_value('_id', '{$p._id}', this);set_hidden_value('gid', '{$p.gid}', this);this.submit">
									{$label_add_person_submit}
								</button>
								&nbsp;&nbsp;
								<button class="btn btn-sm btn-danger" type="submit" name="submit_gs_delete" value="{$label_delete_entry}" onclick="c = confirm('{$label_delete_entry_confirm}'); if(!c) return false; set_action('submit_gs_delete', this);set_hidden_value('_id', '{$p._id}', this);this.submit">
									{$label_delete_entry}
								</button>
							</div>
						</div>
					{/if}
				</div>
			</div>
		</div>
	{/foreach}
{/if}


