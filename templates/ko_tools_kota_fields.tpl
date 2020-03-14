<h3>KOTA-Fields for {$active_table}</h3>

<div class="row">
	<div class="col-sm-6">
		{assign var="input" value=$table_select_input}
		{include file="$ko_path/templates/ko_formular_elements.tmpl"}
	</div>
	<div class="col-sm-6">
		<button type="button" class="btn btn-primary btn-sm full-width" onclick="sendReq('../tools/inc/ajax.php', ['action', 'table', 'sesid'], ['selkotatable', 'ko_leute', kOOL.sid], do_element);">ko_leute</button>
	</div>
</div>

<br>

<h4>DB-Fields without List-Definitions</h4>
{$no_kota_list_fields}
<br><br>

<h4>DB-Fields without Form-Definitions</h4>
{$no_kota_form_fields}
<br><br>

<h4>Form-Layout</h4>
{if $form_layout}
	<pre>
		{$form_layout|@print_r}
	</pre>
{else}
	no form-layout set yet
{/if}
