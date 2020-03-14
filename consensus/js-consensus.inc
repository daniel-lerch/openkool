<script language="javascript" type="text/javascript">
<!--
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
 *  All rights reserved
 *
 *  This script is part of the kOOL project. The kOOL project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  kOOL is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


	$(document).ready(function() {
		$('body').on("click", ".open .button.editable", function() {
			t = this.id.split("_");
			event_id = t[0];
			team_id = t[1];
			person_id = t[2];
			answer = t[3];
			sendReq("../consensus/ajax.php", "action,eventid,teamid,personid,answer,sesid", "addconsensusentry,"+event_id+","+team_id+","+person_id+","+answer+",<?php print session_id(); ?>", do_element_content);
		});
	});
-->
</script>
