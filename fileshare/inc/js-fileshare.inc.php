<script language="javascript" type="text/javascript">
<!--
var postLocation="/fileshare/pgbar.php";
var re = /^(\.php)|(\.sh)/;  // disallow shell scripts and php
var dofilter=true;

function check_types() {
  if(dofilter==false)
    return true;
  with(document.formular) {
    for(i=0 ; i < elements.length ; i++) {
      if(elements[i].value.match(re)) {
        alert('Sorry ' + elements[i].value + ' is not allowed');
        return false;
      }
    }
  }
  return true;
}

function postIt() {

  if(check_types() == false) {
    return false;
  }
  baseUrl = postLocation;
  sid = document.formular.sessionid.value;
  iTotal = escape("-1");
  baseUrl += "?iTotal=" + iTotal;
  baseUrl += "&iRead=0";
  baseUrl += "&iStatus=1";
  baseUrl += "&sessionid=" + sid;

  ko_popup(baseUrl,460,140);
  document.formular.submit();
}

function CreateProjectExplorer() {
  // This MUST be the called before
  // any other of treeview's funcitons.
  Initialise();

<?php
	$folders = ko_fileshare_get_folders($_SESSION["ses_userid"], "view");
	foreach($folders as $f) {
		$d_name = format_userinput($f["name"], "js");
		$share = ($f["share_users"] != "" || $f["flag"] == "S") ? "_shared" : "";
		$d_image = db_get_count("ko_fileshare", "id", "AND `parent` = '".$f["id"]."'" ) > 0 ? "../images/tv_inbox".$share.".gif" : "../images/tv_folder".$share.".gif";
		$d_parent = $f["parent"] == 0 ? "rootCell" : "d_".$f["parent"];
		$d_varname = "d_".$f["id"];
		$d_url = "index.php?action=show_folder&id=".$f["id"];
		$d_fontweight = $f["id"] == $_SESSION["folderid"] ? "1" : "0";

 		print $d_varname.' = CreateTreeItem('.$d_parent.', "'.$d_image.'", "'.$d_image.'", "'.$d_name.'", "'.$d_url.'", "_top", "'.$d_fontweight.'" );'."\n";
	}//foreach(folders as f)
?>
}

-->
</script>
