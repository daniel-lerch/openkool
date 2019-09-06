<tr><td colspan="3">
<br /><br />
<table class="footer" width="100%"> <tr>
<td style="text-align: left; width: 33%;">
<a href="https://github.com/daniel-lerch/openkool"><?=getLL('kool')?></a>
</td>

<td style="text-align: center; width: 33%;">
<?php
print strftime("%A&nbsp;-&nbsp;%x&nbsp;-&nbsp;%X");
?>
</td>

<td style="text-align: right; width: 33%;">
<?php
$help = ko_get_help($ko_menu_akt, "");
print $help["link"];
?>
</td>

</tr></table>


</td></tr>
