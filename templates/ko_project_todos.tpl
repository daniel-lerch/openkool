<div id="project_todo" name="project_todo" style="margin-top:20px;">
<b>Todos</b>
<div onclick="change_vis_tr('new_todo_entry');">Neuer Eintrag</div>
<table width="100%" border="0">
<tr>
	<th class="ko_list">&nbsp;</th>
	<th class="ko_list">&nbsp;</th>
	{if $show_project}
		<th class="ko_list">Projekt</th>
	{/if}
	<th class="ko_list">Prio</th>
	<th class="ko_list">Typ</th>
	<th class="ko_list">Titel</th>
	<th class="ko_list">Beschreibung</th>
	<th class="ko_list">Erstellt</th>
	<th class="ko_list">Erledigt</th>
</tr>

<tr style="display:none;" id="new_todo_entry" name="new_todo_entry">
	<td colspan="2">&nbsp;</td>
	{if $show_project}
		<td>&nbsp;</td>
	{/if}
	<td valign="top" align="center"><select name="new_todo_entry[priority]" size="0">
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
	</select></td>
	<td valign="top" align="center"><select name="new_todo_entry[type]" size="0">{html_options values=$todo_entry.types output=$todo_entry.types}</select></td>
	<td valign="top" align="center"><input type="text" name="new_todo_entry[title]" value="" /></td>
	<td valign="top" align="center"><textarea name="new_todo_entry[description]" cols="50" rows="4"></textarea></td>
	<td valign="top" align="center"><input type="text" name="new_todo_entry[crdate]" value="{$todo_entry.crdate}" /></td>
	<td valign="top" align="center"><input type="submit" name="submit_new_todo_entry" value="{$todo_entry.label_save}" onclick="set_action('submit_new_todo_entry', this);"></td>
</tr>

{foreach from=$project.todo item=todo}
	<tr class="{cycle values="ko_list_even, ko_list_odd"}">
		<td><input type="image" src="{$ko_path}images/button_edit.gif" onclick="change_vis_tr('edit_todo_entry_{$todo.id}');return false;" /></td>
		<td><input type="image" src="{$ko_path}images/button_delete.gif" onclick="set_action('delete_todo', this);set_hidden_value('id', '{$todo.id}', this)" /></td>
		{if $show_project}
			<td><a href="?action=show_project&amp;project_id={$todo.project_id}"><b>{$todo.project_name}</b></a></td>
		{/if}
		<td>{$todo.priority}</span></td>
		<td>{$todo.type}</td>
		<td>{$todo.title}</td>
		<td>{$todo.description}</td>
		<td>{$todo.crdate}</td>
		<td>{if $todo.donedate != '0000-00-00 00:00:00'}{$todo.donedate}{else}&nbsp;{/if}</td>
	</tr>

	<tr style="display:none;" id="edit_todo_entry_{$todo.id}" name="edit_todo_entry_{$todo.id}">
	<td colspan="2">&nbsp;</td>
	{if $show_project}
		<td valign="top" align="center">{$todo.project_name}</td>
	{/if}
	<td valign="top" align="center"><select name="edit_todo_entry[{$todo.id}][priority]" size="0">
	<option value="1" {if $todo.priority == 1}selected="selected"{/if}>1</option>
	<option value="2" {if $todo.priority == 2}selected="selected"{/if}>2</option>
	<option value="3" {if $todo.priority == 3}selected="selected"{/if}>3</option>
	<option value="4" {if $todo.priority == 4}selected="selected"{/if}>4</option>
	<option value="5" {if $todo.priority == 5}selected="selected"{/if}>5</option>
	</select></td>
	<td valign="top" align="center"><select name="edit_todo_entry[{$todo.id}][type]" size="0">{html_options values=$todo_entry.types output=$todo_entry.types selected=$todo.type}</select></td>
	<td valign="top" align="center"><input type="text" name="edit_todo_entry[{$todo.id}][title]" value="{$todo.title}" /></td>
	<td valign="top" align="center"><textarea name="edit_todo_entry[{$todo.id}][description]" cols="50" rows="3">{$todo.description}</textarea></td>
	<td valign="top" align="center">&nbsp;</td>
	<td valign="top" align="center"><input type="checkbox" name="edit_todo_entry[{$todo.id}][done]" value="1" {if $todo.done}checked="checked"{/if} /><br /><input type="submit" name="submit_edit_todo_entry" value="Speichern" onclick="set_action('submit_edit_todo_entry', this); set_hidden_value('id', '{$todo.id}', this);"></td>
	</tr>

{/foreach}
</table>
</div>
