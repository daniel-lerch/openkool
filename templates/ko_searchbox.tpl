{if $searchbox.general_input && !$searchbox_only}
	{if !$hide_li}<li style="padding-right:10px;" id="general-search-li" name="general-search-li">{/if}
		<table style="height:100%;">
			<tr>
				<td style="vertical-align:middle;">
					<div id="general-search-container">
						<div class="form-group nomargin">
							{$searchbox.general_input.code}
						</div>
					</div>
				</td>
			</tr>
		</table>
	{if !$hide_li}</li>{/if}
{/if}
{if $searchbox.inputs && $searchbox.inputs|@count > 0 && !$general_only}
	{if !$hide_li}<li id="searchbox-li" name="searchbox-li">{/if}
		<a href="#" class="{if $searchbox.has_active_filters}danger{/if}" data-toggle="popover" id="searchbox-inputs-btn"><span class="fa fa-search icon-line-height"></span></a>
		<div id="searchbox-inputs-content-script" style="display:none;">
			<div id="searchbox-inputs">
				{if !$searchbox.hide_form}<form>{/if}
					{foreach from=$searchbox.inputs key="k" item="input"}
						{if $input.html}
							{$input.html}
						{else}
							<div class="form-group nomargin">
								{if $input.label}<label>{$input.label}</label>{/if}
								{$input.code}
							</div>
						{/if}
					{/foreach}
					{if !$searchbox.hide_buttons}
						<div class="btn-group btn-group-sm">
							<button id="submit-search-btn" class="btn btn-danger" type="submit">{ll key="reset"}&nbsp;<i class="fa fa-remove"></i></button>
							<button id="clear-search-btn" class="btn btn-primary" type="submit">{ll key="ok"}&nbsp;<i class="fa fa-check"></i></button>
						</div>
					{/if}
				{if !$searchbox.hide_form}</form>{/if}
			</div>
		</div>
		<script>
			$('body').on('click', '#searchbox-inputs-btn[data-toggle="popover"]', function(event) {ldelim}
				$('.popover[role="tooltip"].in').remove();
				var content = $('#searchbox-inputs-content-script').html();
				$(this).popover({ldelim}
					trigger: 'manual',
					html: 'true',
					container: '#main_content',
					template: '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>',
					content: content,
					placement: 'bottom'
					{rdelim});
				$(this).popover('show');
				return false;
				{rdelim});
		</script>
	{if !$hide_li}</li>{/if}
{/if}

