<h3>{$tpl_fm_title}
	{if $help.show}<span>{$help.link}</span>{/if}
</h3>


<input type="hidden" name="aa_id" id="aa_id" value="" />

{if $tpl_aa_empty}
	{$label_empty}
{else}
	{foreach name=mutationen item=mutation from=$tpl_mutationen}
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>{$mutation.name}</h4>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-8 col-md-offset-2">
						{if $mutation.family != null}
							<div class="notification notification_info" style="margin-bottom:10px;">{ll key="leute_labels_mutations_is_in_family"}: {$mutation.family.id}. {ll key="leute_warning_mutations_family"}.</div>
						{/if}

						<table class="full-width table table-condensed">
							{if $mutation.family != null}
								<tr>
									<td style="vertical-align:middle;">{ll key="family"}:</td>
									<td style="vertical-align:middle;">&nbsp;</td>
									<td style="vertical-align:middle;">{$mutation.family.id}</td>
									<td style="vertical-align:middle;">&nbsp;</td>
									<td style="padding:6px;vertical-align:middle;"><input style="margin-top:0;" type="checkbox" id="chk_{$mutation.id}[decouple_from_family]" name="chk_{$mutation.id}[decouple_from_family]" /></td>
									<td style="vertical-align:middle;"><label for="chk_{$mutation.id}[decouple_from_family]">{ll key="leute_labels_mutations_decouple_from_family"}</label></td>
								</tr>
							{/if}
							{foreach item=field from=$mutation.fields}
								<tr>
									<td style="vertical-align:middle;">
										{if $mutation.family != null && $field.isFamilyField}<img src="{$ko_path}images/icon_familie.png" title="{ll key="leute_labels_mutations_family_title"}" /> {/if}{$field.desc}:
									</td>
									<td style="width:1%;padding:6px;vertical-align:middle;">
										<input style="margin-top:0;" type="radio" value="0" name="chk_{$mutation.id}[{$field.name}]"
												{if $field.readonly || ($field.newvalue == "" || $field.newvalue == "0000-00-00" || $field.newvalue == "0")}
													checked
												{/if}
												{if $field.readonly}disabled="disabled"{/if}
												/>
									</td>
									<td style="vertical-align:middle;">
										{$field.oldvalue}
									</td>
									<td style="width: 1%; vertical-align:middle;">
										&nbsp;&rarr;&nbsp;
									</td>
									<td style="width:1%;padding:6px;vertical-align:middle;">
										<input style="margin-top:0;" type="radio" value="1" name="chk_{$mutation.id}[{$field.name}]"
												{if !$field.readonly && ($field.newvalue != "" && $field.newvalue != "0000-00-00" && $field.newvalue != "0")}
													checked
												{/if}
												{if $field.readonly}disabled="disabled"{/if}
												/>
									</td>
									<td style="vertical-align:middle;">
										{if $field.type == 'select'}
											<select class="input-sm form-control" name="txt_{$mutation.id}[{$field.name}]" size="0" {if $field.readonly}disabled="disabled"{/if}>
												{foreach from=$field.values item=v key=k}
													<option value="{$v}" {if $v == $field.newvalue}selected="selected"{/if}>{if $field.descs.$k}{$field.descs.$k}{else}{$field.descs.$v}{/if}</option>
												{/foreach}
											</select>
										{else}
											<input class="input-sm form-control" type="text" name="txt_{$mutation.id}[{$field.name}]" value="{$field.newvalue}" {if $field.readonly}disabled="disabled"{/if} />
										{/if}
									</td>
								</tr>
							{/foreach}
							<tr>
								<td>{$label_crdate}:</td>
								<td colspan="5">{$mutation.crdate} {$mutation.cruserid}</td>
							</tr>
						</table>

						{if $mutation.bemerkung != ""}
							<br /><b>{$label_comments}:</b><br />
							{$mutation.bemerkung}
							<br />
						{/if}

						<p align="center">
							<button class="btn btn-sm btn-primary" type="submit" name="submit_mutation" value="{$label_submit}" onclick="set_action('submit_mutation', this);set_hidden_value('aa_id', '{$mutation.id}', this);this.submit">{$label_submit}</button>
							<button class="btn btn-sm btn-danger" type="submit" name="submit_del_mutation" value="{$label_delete}" onclick="set_action('submit_del_mutation', this);set_hidden_value('aa_id', '{$mutation.id}', this);this.submit">{$label_delete}</button>
						</p>
					</div>
				</div>
			</div>
		</div>
	{/foreach}
{/if}


