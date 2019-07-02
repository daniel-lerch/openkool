<script language="javascript" type="text/javascript">
<!--

//Session timeout message
function session_time_down(first) { 
	var check_intervall = 60;   //Checking intervall
	var time_warning    = 180;  //Time in seconds before the session timeout when the warning should be displayed
	var timeout_url     = '<?php print $BASE_URL."index.php"; ?>';
	var auto_logout     = <?php print SESSION_TIMEOUT_AUTO_LOGOUT ? 'true' : 'false'; ?>;
	var show_warning    = <?php print SESSION_TIMEOUT_WARNING ? 'true' : 'false'; ?>;

	if(!first) session_timeout -= check_intervall;

	if(session_timeout <= 0) {
		if(auto_logout) {
			window.location.href = timeout_url;
		} else {
			msg = document.getElementsByName('session_timeout')[0];
			msg.innerHTML = '<b><?php print getLL("session_timedout"); ?></b>';
			msg.style.visibility = "visible";
			msg.style.display = "block";
			return;
		}
	} else if(session_timeout <= time_warning && show_warning) {
		msg = document.getElementsByName('session_timeout')[0];
		msg.style.visibility = "visible";
		msg.style.display = "block";
	}
	down = setTimeout("session_time_down(false)", check_intervall*1000);
}

var session_timeout = <?php print SESSION_TIMEOUT; ?>;

function session_time_init() {
	<?php if($ko_menu_akt == "install" || !$_SESSION["ses_userid"] || $_SESSION["ses_userid"] == ko_get_guest_id()) print 'return;'; ?>
	if(session_timeout <= 0) return;
	session_time_down(true);
}


-->
</script>
