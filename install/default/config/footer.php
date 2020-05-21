<div id="footer">
	<table>
		<tr>

			<td align="left" width="33%">
				<a href="http://www.churchtool.org"><?= getLL('kool') ?></a>
			</td>

			<td align="center" width="33%">
				<?php
				print strftime("%A&nbsp;-&nbsp;%x&nbsp;-&nbsp;%X");
				?>
			</td>

			<td align="right" width="33%">
				<?php
				$help = ko_get_help($ko_menu_akt, "");
				print $help["link"];
				?>
			</td>

		</tr>
	</table>
</div>