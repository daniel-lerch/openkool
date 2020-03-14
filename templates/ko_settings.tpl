<h3>{$tpl_titel}{if $help.show} {$help.link}{/if}</h3>

<div class="subpart">

	{section name=part loop=$tpl_parts}
		<div class="panel panel-default">
			<div class="panel-heading block_header">
				<h3 class="panel-title">{$tpl_parts[part].titel}</h3>
			</div>
			<div class="panel-body block_content">
				<div class="form-horizontal">
				{foreach name=settings item=setting from=$tpl_parts[part].settings}
					<div class="form-group">
						{if $setting.type != "checkbox"}
						<label for="{$setting.name}" class="col-sm-2">{$setting.desc}</label>
						{/if}
						<div class="col-sm-10{if $setting.type == "checkbox"} col-sm-offset-2{/if}">

							{if $setting.type == "text"}
								<input type="text" class="input-sm form-control" name="{$setting.name}" value="{$setting.value}" {$setting.params} />

							{elseif $setting.type == "password"}
								<input type="password" class="input-sm form-control" name="{$setting.name}" value="{$setting.value}" {$setting.params} />

							{elseif $setting.type == "textarea"}
								<textarea name="{$setting.name}" class="input-sm form-control" {$setting.params}>{$setting.value}</textarea>

							{elseif $setting.type == "checkbox"}
								<div class="checkbox">
									<label>
										<input type="checkbox" name="{$setting.name}" value="1" {$setting.params}>
										{$setting.desc}
									</label>
								</div>

							{elseif $setting.type == 'switch'}
								<input type="hidden" name="{$setting.name}" id="{$setting.name}" value="{$setting.value}" />{$setting.desc2}
								<div class="input_switch switch_state_{$setting.value}" name="switch_{$setting.name}">
									<label class="switch_state_label_0" {if $setting.value == 1}style="display: none;"{/if}>{$setting.label_0}</label>
									<label class="switch_state_label_1" {if $setting.value == 0}style="display: none;"{/if}>{$setting.label_1}</label>
								</div>

							{elseif $setting.type == "select"}
								<select class="input-sm form-control" name="{$setting.name}" {$setting.params}>
									{foreach from=$setting.values item=v key=k}
										<option value="{$v}" {if $v == $setting.value}selected="selected"{/if}>{$setting.descs.$k}</option>
									{/foreach}
								</select>

							{elseif $setting.type == "radio"}
								{html_radios name=$setting.name values=$setting.value checked=$setting.checked output=$setting.output separator="&nbsp;&nbsp; &nbsp;"}

							{elseif $setting.type == "doubleselect"}
								<table><tr><td>
											<input type="hidden" name="{$setting.name}" value="{$setting.avalue}" />
											<input type="hidden" name="old_{$setting.name}" value="{$setting.avalue}" />
											<select name="sel_ds1_{$setting.name}" {$setting.params} onclick="double_select_add(this.options[parseInt(this.selectedIndex)].text, this.options[parseInt(this.selectedIndex)].value, 'sel_ds2_{$setting.name}', '{$setting.name}');">
												{foreach from=$setting.values item=v key=k}
													<option value="{$v}">{$setting.descs.$k}</option>
												{/foreach}
											</select>
										</td><td valign="top">
											{if $setting.show_moves}
												<img src="{$ko_path}images/ds_top.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'top');" /><br />
												<img src="{$ko_path}images/ds_up.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'up');" /><br />
												<img src="{$ko_path}images/ds_down.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'down');" /><br />
												<img src="{$ko_path}images/ds_bottom.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'bottom');" /><br />
												<img src="{$ko_path}images/ds_del.gif" border="0" alt="up" onclick="double_select_move('{$setting.name}', 'del');" />
											{else}
												<img src="{$ko_path}images/button_delete.gif" alt="{$label_doubleselect_remove}" title="{$label_doubleselect_remove}" border="0" onclick="double_select_move('{$setting.name}', 'del');"/>
											{/if}
										</td><td>
											<select name="sel_ds2_{$setting.name}" {$setting.params}>
												{foreach from=$setting.avalues item=v key=k}
													<option value="{$v}">{$setting.adescs.$k}</option>
												{/foreach}
											</select>
										</td></tr></table>

							{elseif $setting.type == "html"}
								{$setting.value}

							{/if}
						</div>
					</div>
				{/foreach}
				</div>
			</div>
		</div>

	{/section}
	</table>

	<div class="btn-field">
		<button type="submit" class="btn btn-primary" name="submit" value="{$label_save}" onclick="set_action('{$tpl_action}', this)">
			{$label_save} <i class="fa fa-save"></i>
		</button>
		<button type="reset" class="btn btn-danger" name="cancel" value="{$label_reset}">
			{$label_reset}
		</button>
	</div>

</div>
