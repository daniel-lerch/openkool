<h3>{$tpl_title}
		{if $help.show}<span>{$help.link}</span>{/if}
</h3>

<input type="hidden" name="leute_revision_id" id="leute_revision_id" value="">
<input type="hidden" name="leute_revision_person_id" id="leute_revision_person_id" value="">

{if $tpl_leute_revisions_empty}
	{$label_empty}
{else}
	{foreach name=revisions item=revision from=$tpl_mods}
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h4 class="panel-title">
					{$revision.p_label} ({ll key="leute_revisions_reason"}: {ll key="leute_revisions_reason_`$revision.r.reason`"})
				</h4>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-6">
						<div class="list-group">
							<div class="list-group-item list-group-item-info">
								{ll key="leute_revisions_data_fields"}
							</div>
							{foreach item=input from=$revision.p_fields}
								<div class="list-group-item">
									<div class="row">
										<label class="col-sm-3 control-label nomargin">
											{$input.desc}
										</label>
										<div class="col-sm-7">
											{include file="$ko_path/templates/ko_formular_elements.tmpl"}
										</div>
									</div>
								</div>
							{/foreach}
						</div>
					</div>
					<div class="col-md-6">
						<div class="list-group">
							<div class="list-group-item list-group-item-info">
								{ll key="leute_revisions_assign_fields"}
							</div>
							<div class="list-group-item">
								<div class="formular_header">
									<label>{ll key="leute_revisions_list_add_to_suggested_person"}</label>
								</div>
								{foreach item=dbp from=$revision.db}
									<div class="input-group{if $dbp.hidden} inactive{/if}">
										<div class="input-group-addon" style="width:auto;" data-toggle="tooltip" data-html="true" data-container="body" title="{$dbp.adressdaten}" >{if $dbp.firm}{$dbp.firm}{/if} {if $dbp.department}({$dbp.department}){/if} {$dbp.name}</div>
										<div class="input-group-btn">
											<button class="btn btn-sm btn-primary" type="submit" name="submit" value="{$label_submit}" onclick="set_action('submit_leute_revision', this);set_hidden_value('leute_revision_id', '{$revision.id}', this);set_hidden_value('leute_revision_person_id', '{$dbp.lid}', this);this.submit">{$label_submit}</button>
										</div>
									</div>
								{/foreach}
							</div>
							<div class="list-group-item">
								<div class="formular_header">
									<label>{ll key="leute_revisions_list_add_to_selected_person"}</label>
								</div>
								<table class="full-width">
									<tr>
										<td style="padding-right: 3px;">
											{assign var="input" value=$revision.selectedPerson}
											{include file="$ko_path/templates/ko_formular_elements.tmpl"}
										</td>
										<td style="width:1%;">
											<button class="btn btn-sm btn-success" type="submit" name="submit" value="{$label_submit}" onclick="set_action('submit_leute_revision', this);set_hidden_value('leute_revision_id', '{$revision.id}', this);set_hidden_value('leute_revision_person_id', 'selected', this);this.submit">{$label_submit}</button>
										</td>
									</tr>
								</table>
							</div>
							<div class="list-group-item">
								<button class="btn btn-sm btn-warning" type="submit" name="submit" value="{$label_delete}" onclick="c=confirm('{$label_confirm_delete}'); if(!c) return false;set_action('submit_del_leute_revision', this);set_hidden_value('leute_revision_id', '{$revision.id}', this);this.submit">{$label_delete}</button>
								{if $showDeleteAddress}
									<button class="btn btn-sm btn-danger" style="margin-left: 20px;" type="submit" name="submitDel" value="{ll key="leute_revisions_list_delete_address"}" onclick="c=confirm('{ll key="leute_confirm_del_pers"}'); if(!c) return false;set_action('submit_del_leute_revision_address', this);set_hidden_value('leute_revision_id', '{$revision.id}', this);this.submit">{ll key="leute_revisions_list_delete_address"}</button>
								{/if}
							</div>
						</div>
					</div>
				</div>
				<p>{$label_crdate}: {$revision.crdate} {$revision.cruser}</p>
			</div>
		</div>
	{/foreach}
{/if}

