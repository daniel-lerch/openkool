<script language="javascript" type="text/javascript">
<!--
function detectMouseButton(event) {
	button = 0;
	if(window.event) {  //IE
		button = window.event.button;
	} else if(event.which) {  //Mozilla
		button = event.which;
	}
	return button;
}

function addOne(id) {
	for (i=0; i<document.formular.length;i++) {
	  obj = document.formular.elements[i];
    if (obj.type == "text" && obj.name == "txt["+id+"]") {
      if(!obj.value) obj.value = 1;
      else obj.value = Math.abs(obj.value)+1;
    }
  }
}

function subOne(id) {
	for (i=0; i<document.formular.length;i++) {
	  obj = document.formular.elements[i];
    if (obj.type == "text" && obj.name == "txt["+id+"]") {
      if(!obj.value || obj.value <= 1) obj.value = '';
      else obj.value = Math.abs(obj.value)-1;
    }
  }
}
-->
</script>
