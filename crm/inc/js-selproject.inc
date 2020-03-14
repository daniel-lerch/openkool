<script language="javascript" type="text/javascript">
<!--
function Project(status) {
	this.status = JSON.parse(status);
}

function selProject(pid) {

	var projects = new Object();
	<?php

	$allStatus = db_select_data("ko_crm_status", "WHERE 1=1", "*");

	//Build code for event groups with their data
	$code = "";

	$code .= sprintf("projects['%s'] = new Project('%s');\n",
										 '',
										 '[]'
										 );
	$projects = db_select_data("ko_crm_projects", "WHERE 1=1", "*");
	foreach($projects as $project) {
		if($access['crm']['ALL'] < 2 && $access['crm'][$project['id']] < 2) continue;
		$status = array(array('id' => '', 'title' => ''));
		if($project["status_ids"]) {
			foreach(explode(",", $project["status_ids"]) as $sid) {
				$status[] = array('id' => $sid, 'title' => $allStatus[$sid]["title"], 'deadline' => $allStatus[$sid]['default_deadline']);
			}
		}
		$code .= sprintf("projects['%s'] = new Project('%s');\n",
										 $project["id"],
										 json_encode($status)
										 );
	}
	print $code;
	?>

	var koi_status_id = $("[name*='status_id']");

	if(projects[pid]) {

		var old_val = koi_status_id.val()
		koi_status_id.empty();
		koi_status_id.val('');

		var do_old_val = false;

		if(projects[pid].status) {
			for(var i=0; i<projects[pid].status.length; i++) {
				koi_status_id.append($('<option></option>').attr('value', projects[pid].status[i].id).text(projects[pid].status[i].title));
				if (projects[pid].status[i].id == old_val) do_old_val = true;
			}
		}

		if (do_old_val) {
			koi_status_id.val(old_val);
		}

	}
}


function selStatus(pid) {
	<?php

	$allStatus = db_select_data("ko_crm_status", "WHERE 1=1", "*");

	//Build code for event groups with their data
	$code = "var defaultDeadlines = [];";

	foreach($allStatus as $status) {
		$code .= "defaultDeadlines['".$status['id']."'] = '".$status['default_deadline']."';\n";
	}
	print $code;
	?>


	deadlineDays = defaultDeadlines[pid];

	var koi_deadline = $("[name*='deadline']");
	if(deadlineDays > 0) {
		var today = new Date();
		deadlineDate = new Date(
			today.getFullYear(),
			today.getMonth(),
			today.getDate() + parseInt(deadlineDays)
		);

		var day = deadlineDate.getDate();
		var monthIndex = deadlineDate.getMonth();
	  var year = deadlineDate.getFullYear();
		deadline = day+"."+(monthIndex+1)+"."+year;

		koi_deadline.val(deadline);
	} else {
		koi_deadline.val('');
	}
}

-->
</script>
