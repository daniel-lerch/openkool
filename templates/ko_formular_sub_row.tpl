<div class="row">
	{assign var="input" value=$row.inputs.0}
	{assign var="columnWidthComp" value=0}
	{if !$input.columnWidth}
		{assign var="elemsInRow" value=$row.inputs|@sizeof }
		{if $elemsInRow > 2}
			{assign var="columnWidthComp" value=$bootstrap_cols_per_row/$elemsInRow}
		{else}
			{assign var="columnWidthComp" value=$bootstrap_cols_per_row/2}
		{/if}
	{/if}
	{foreach name=inputs item=input from=$row.inputs}
		{if $columnWidthComp == 0}
			{assign var="columnWidth" value=$input.columnWidth}
		{else}
			{assign var="columnWidth" value=$columnWidthComp}
		{/if}

		{if $input.customRowColumns}
			<div class="formular-cell {$input.customRowColumns}">
		{else}
			<div class="formular-cell col-md-{$columnWidth} col-sm-{math equation="x / 2" x=$bootstrap_cols_per_row}">
		{/if}
			<div class="formular_header{if $input.headerclass} {$input.headerclass}{/if}">
				{if $input.descicon}
					{if $input.descicon.icon}
						{assign var="desciconContext" value=""}
						{if $input.descicon.context}
							{assign var="desciconContext" value=" text-`$input.descicon.context`"}
						{/if}
						<i class="fa fa-{$input.descicon.icon}{$desciconContext}"></i>
					{else}
						<i class="fa fa-{$input.descicon}"></i>
					{/if}
					&nbsp;
				{/if}
				{if $input.descimg}<img src="{$ko_path}images/{$input.descimg}" style="border:none;" />{/if}
				{if $input.title_pre_html}{$input.title_pre_html}{/if}
				<label for="{$input.name}">{$input.desc}{if $input.is_mandatory} *{/if}</label>{if $input.help}&nbsp;{$input.help}{/if}
			</div>
			<div class="formular_content{if $input.contentclass} {$input.contentclass}{/if}">
				{if $input.type == "_save"}
					<p style="text-align:center;">
						{if $tpl_special_submit}
							{$tpl_special_submit}
						{else}
							<button type="submit" class="btn btn-primary" name="submit" class="ko_form_submit {$submit_class}" value="{$tpl_submit_value}" onclick="{$tpl_onclick_action}set_action('{$tpl_action}', this)">
								{$tpl_submit_value} <i class="fa fa-save"></i>
							</button>
						{/if}
						{if !$tpl_hide_cancel}
							<button type="submit" class="btn btn-danger" name="cancel" value="{$label_cancel}" onclick="set_action('{$tpl_cancel}', this);">
								{$label_cancel} <i class="fa fa-remove"></i>
							</button>
						{/if}
						{if $tpl_submit_as_new && !$force_hide_submit_as_new}
							<br />
							<button type="submit" class="btn btn-success" name="submit_as_new" value="{$tpl_submit_as_new}" onclick="set_action('{$tpl_action_as_new}', this);">
								{$tpl_submit_as_new} <i class="fa fa-plus"></i>
							</button>
						{/if}
					</p>
				{elseif $input.type == "_sep"}
					<p></p>
				{else}
					{include file="$ko_path/templates/ko_formular_elements.tmpl"}
				{/if}
			</div>
		</div>
	{/foreach}
</div>
