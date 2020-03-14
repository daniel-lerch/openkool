{if $layout_mode == 'form_row'}
	<tr class="ko-js-table-form-row" data-id="{$id}">
		<td>
			<script>$('input[name="id"]').val('{$hash}');</script>
			<input type="hidden" class="leute-crm-entry-{$id}-form-element" name="koi[ko_crm_contacts][leute_ids][{$id}]" value="{$parent_row_id}">
			<button type="button" class="icon icon-line-height leute-crm-entries-{if $mode == 'new'}add{else}edit{/if}-entry-submit-btn" title="{ll key="save"}"><i class="fa fa-save"></i></button>
		</td>
		<td>
			<button type="button" class="icon icon-line-height leute-crm-entries-exit-form-btn" title="{ll key="cancel"}"><i class="fa fa-stop"></i></button>
		</td>
		{foreach item=input from=$inputs}
			<td>
				{if $input}
					{include file="$ko_path/templates/ko_formular_elements.tmpl"}
				{/if}
			</td>
		{/foreach}
	</tr>


{else}
	{if !$rows_only}
		<h4>{ll key="crm_contacts_list_title"} {$person.firm} {$person.vorname} {$person.nachname}</h4>
		<div class="full-width ko-js-table-container" data-parent-row-id="{$parent_row_id}">
			<div class="row" style="margin-top:10px;margin-bottom:10px;">
				<div class="col-sm-4 crm-project-filter">
					<b>{ll key="kota_listview_ko_crm_contacts_project_id"}</b>
					{foreach from=$projects key=id item=project}
						<a class="label label-default ko-js-table-filter-item" data-filter-value="{$id}" data-filter-col="project_id" data-filter-target="#leute-crm-entries-{$parent_row_id}" title="{ll key="crm_filter_by_project"} = {$project}">{$project}</a>
					{/foreach}
				</div>
				<div class="col-sm-4 crm-status-filter">
					<b>{ll key="kota_listview_ko_crm_contacts_status_id"}</b>
					{foreach from=$status key=id item=statusEntry}
						<a class="label label-default ko-js-table-filter-item" data-filter-value="{$id}" data-filter-col="status_id" data-filter-target="#leute-crm-entries-{$parent_row_id}" title="{ll key="crm_filter_by_status"} = {$statusEntry}">{$statusEntry}</a>
					{/foreach}
				</div>
				<div class="col-sm-4 crm-cruser-filter">
					<b>{ll key="kota_listview_ko_crm_contacts_cruser"}</b>
					<select class="crm-cruser-filter-select">
						{foreach from=$crusers key=id item=cruser}
							<option value="{$id}" title="{ll key="crm_filter_by_creator"} = {$cruser}">{$cruser}</option>
						{/foreach}
					</select>
					<i class="crm-cruser-filter-cruser-item ko-js-table-filter-item" data-filter-value="" data-filter-col="cruser" data-filter-target="#leute-crm-entries-{$parent_row_id}" style="display:none;"></i>
					<i class="crm-cruser-filter-admingroups-item ko-js-table-filter-item" data-filter-value="" data-filter-col="admingroups" data-filter-target="#leute-crm-entries-{$parent_row_id}" data-filter-match="substring" style="display:none;"></i>
				</div>
			</div>
			<table class="table table-bordered table-condensed full-width ko-js-table" id="leute-crm-entries-{$parent_row_id}" data-cols="{','|implode:$data.header}">
				<tr class="bg-success">
					<th style="vertical-align:middle;width:30px;">
						<button type="button" class="icon icon-line-height leute-crm-entries-add-entry-form-btn" title="{ll key="crm_contacts_form_title_new"}"><i class="fa fa-plus"></i></button>
					</th>
					<th style="width:30px;"></th>
					{foreach from=$data.header item=header_entry}
						<th>{$header_entry}</th>
					{/foreach}
				</tr>
	{/if}
				{foreach from=$data.data item=row}
					<tr class="ko-js-table-data-row" data-id="{$row.value.id}"{foreach from=$data.header key=header_key item=header_entry} data-col-{$header_key}="{$row.value.$header_key}"{/foreach}{foreach from=$row.hidden_values key=hidden_key item=hidden_value} data-col-{$hidden_key}="{$hidden_value}"{/foreach}>
						<td>
							{if $row.edit}
								<button type="button" class="icon icon-line-height leute-crm-entries-edit-entry-form-btn" title="{ll key="crm_contacts_form_title_edit"}"><i class="fa fa-edit"></i></button>
							{/if}
						</td>
						<td>
							{if $row.delete}
								<button type="button" class="icon icon-line-height leute-crm-entries-delete-entry-btn" data-confirm-delete-label="{ll key="list_label_confirm_delete"}" title="{ll key="delete"}"><i class="fa fa-remove"></i></button>
							{/if}
						</td>
						{foreach from=$data.header key=header_key item=header_entry}
							{assign var="col_name" value=$header_key}
							{assign var="col_value" value=$row.value.$header_key}
							{assign var="col_processed_value" value=$row.processed_value.$header_key}
							<td data-col="{$col_name}" data-value="{$col_value}">{$col_processed_value}</td>
						{/foreach}
					</tr>
				{/foreach}
	{if !$rows_only}
			</table>
		</div>
	{/if}
{/if}
