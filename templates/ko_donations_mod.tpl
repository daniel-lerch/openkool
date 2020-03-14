<h3>{ll key="donations_mod_title"}</h3>

<input type="hidden" name="donations_mod_id" id="donations_mod_id" value="">
<input type="hidden" name="donations_mod_person_id" id="donations_mod_person_id" value="">

{if $tpl_donations_mod_empty}
	{$label_empty}
{else}
	{foreach name=donations item=donation from=$tpl_mods}
		<div class="panel panel-primary">
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-4 col-md-6">
						<div class="list-group">
							<div class="list-group-item list-group-item-info">
								{ll key="donations_mod_donations_fields"}
							</div>
							{foreach item=input from=$donation.d_fields}
								<div class="list-group-item">
									<div class="row">
										<label class="col-sm-4 control-label nomargin">
											{$input.desc}
										</label>
										<div class="col-sm-8">
											{include file="$ko_path/templates/ko_formular_elements.tmpl"}
										</div>
									</div>
								</div>
							{/foreach}
						</div>
					</div>
					<div class="col-lg-4 col-md-6">
						<div class="list-group">
							<div class="list-group-item list-group-item-info">
								{ll key="donations_mod_person_fields"}
							</div>
							{foreach item=input from=$donation.p_fields}
								<div class="list-group-item">
									<div class="row">
										<label class="col-sm-4 control-label nomargin">
											{$input.desc}
										</label>
										<div class="col-sm-8">
											{include file="$ko_path/templates/ko_formular_elements.tmpl"}
										</div>
									</div>
								</div>
							{/foreach}
						</div>
					</div>
					<div class="col-lg-4 col-md-6">
						<div class="list-group">
							<div class="list-group-item list-group-item-info">
								{ll key="donations_mod_assign_fields"}
							</div>
							{if $donation.db|@sizeof > 0}
								<div class="list-group-item">
									<div class="formular_header">
										<label>{ll key="donations_mod_list_add_to_suggested_person"}</label>
									</div>
									{foreach item=dbp from=$donation.db}
										<div class="input-group">
											<div class="input-group-addon" style="width:auto;" data-toggle="tooltip" data-html="true" data-container="body" title="{$dbp.adressdaten}" >{if $dbp.firm}{$dbp.firm}{/if} {if $dbp.department}({$dbp.department}){/if} {$dbp.name}</div>
											<div class="input-group-btn">
												<button type="submit" class="btn btn-sm btn-primary" name="submit" value="{ll key="donations_mod_list_submit"}" onclick="set_action('submit_donations_mod', this);set_hidden_value('donations_mod_id', '{$donation.id}', this);set_hidden_value('donations_mod_person_id', '{$dbp.lid}', this);this.submit">{ll key="donations_mod_list_submit"}</button>
											</div>
											<div class="input-group-btn">
												<button type="submit" class="btn btn-sm btn-primary" name="submit" value="{ll key="donations_mod_list_submit_and_mutation"}" onclick="set_action('submit_donations_mod_aa', this);set_hidden_value('donations_mod_id', '{$donation.id}', this);set_hidden_value('donations_mod_person_id', '{$dbp.lid}', this);this.submit">{ll key="donations_mod_list_submit_and_mutation"}</button>
											</div>
										</div>
									{/foreach}
								</div>
							{/if}
							<div class="list-group-item">
								<div class="formular_header">
									<label>{ll key="donations_mod_list_add_to_selected_person"}</label>
								</div>
								<table class="full-width">
									<tr>
										<td style="padding-right: 3px;">
											{assign var="input" value=$donation.selectedPerson}
											{include file="$ko_path/templates/ko_formular_elements.tmpl"}
										</td>
										<td style="width:1%;">
											<button type="submit" class="btn btn-sm btn-primary" name="submit" value="{ll key="donations_mod_list_submit"}" onclick="set_action('submit_donations_mod', this);set_hidden_value('donations_mod_id', '{$donation.id}', this);set_hidden_value('donations_mod_person_id', 'selected', this);this.submit">{ll key="donations_mod_list_submit"}</button>
										</td>
										<td style="width:1%;">
											<button type="submit" class="btn btn-sm btn-primary" name="submit" value="{ll key="donations_mod_list_submit_and_mutation"}" onclick="set_action('submit_donations_mod_aa', this);set_hidden_value('donations_mod_id', '{$donation.id}', this);set_hidden_value('donations_mod_person_id', 'selected', this);this.submit">{ll key="donations_mod_list_submit_and_mutation"}</button>
										</td>
									</tr>
								</table>
							</div>
							<div class="list-group-item">
								<div class="formular_header">
									<label>{ll key="donations_mod_list_add_to_new_person"}</label>
								</div>
								<table class="full-width">
									<tr>
										<td style="padding-right: 3px;">
											{$donation.addToGroup.desc}{assign var="input" value=$donation.addToGroup}
											{include file="$ko_path/templates/ko_formular_elements.tmpl"}
										</td>
										<td style="width:1%;">
											<button type="submit" class="btn btn-sm btn-success" name="submit" value="{ll key="donations_mod_list_submit"}" onclick="set_action('submit_donations_mod', this);set_hidden_value('donations_mod_id', '{$donation.id}', this);set_hidden_value('donations_mod_person_id', '0', this);this.submit">{ll key="donations_mod_list_submit"}</button>
										</td>
									</tr>
								</table>
							</div>
							<div class="list-group-item">
								<button type="submit" class="btn btn-sm btn-danger" name="submit" value="{ll key="donations_mod_list_delete"}" onclick="c=confirm('{$label_confirm_delete}'); if(!c) return false;set_action('submit_del_donations_mod', this);set_hidden_value('donations_mod_id', '{$donation.id}', this);this.submit">{ll key="donations_mod_list_delete"}</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	{/foreach}
{/if}
