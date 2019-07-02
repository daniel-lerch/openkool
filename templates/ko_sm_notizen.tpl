<tr><td colspan="3" class="submenu">

<strong><big>&middot;</big></strong>{$notizen_open}<br />
<select name="sel_notiz" size="0" onchange="sendReq('../inc/ajax.php', 'action,pos,id,mod,sesid,selnote', 'opennote,{$sm.position},{$sm.id},{$sm.mod},{$sm.sesid},'+this.options[this.selectedIndex].value, do_element);">
<option value=""></option>
{html_options values=$tpl_notizen_values output=$tpl_notizen_output selected=$tpl_notizen_selected}
</select>
<input type="image" src="{$ko_path}images/icon_open.gif" alt="{$notizen_open}" title="{$notizen_open}" onclick="sendReq('../inc/ajax.php', 'action,pos,id,mod,sesid,selnote', 'opennote,{$sm.position},{$sm.id},{$sm.mod},{$sm.sesid},'+document.formular.sel_notiz.options[document.formular.sel_notiz.selectedIndex].value, do_element); return false;" />
&nbsp;
<input type="image" src="{$ko_path}images/icon_trash.png" alt="{$notizen_delete}" title="{$notizen_delete}" onclick="c = confirm('{$notizen_delete_confirm}');if(!c) return false;sendReq('../inc/ajax.php', 'action,pos,id,mod,sesid,selnote', 'deletenote,{$sm.position},{$sm.id},{$sm.mod},{$sm.sesid},'+document.formular.sel_notiz.options[document.formular.sel_notiz.selectedIndex].value, do_element); return false;" />


<table><tr><td valign="top">
<textarea name="txt_notiz" cols="18" rows="7">{$tpl_text}</textarea>
</td></tr></table>


<strong><big>&middot;</big></strong>{$notizen_save}<br />
<input type="text" name="txt_notiz_new" size="12" maxlength="25" />
<input type="image" src="{$ko_path}images/icon_save.gif" alt="{$notizen_save}" title="{$notizen_save}" onclick="sendReq('../inc/ajax.php', 'action,pos,id,mod,sesid,notename,selnote,note', 'savenote,{$sm.position},{$sm.id},{$sm.mod},{$sm.sesid},'+document.formular.txt_notiz_new.value+','+document.formular.sel_notiz.options[document.formular.sel_notiz.selectedIndex].value+','+document.formular.txt_notiz.value, do_element); return false;" />

</td>
</tr>

<tr><td colspan="3"><br /></td></tr>
