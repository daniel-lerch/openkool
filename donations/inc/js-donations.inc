<script language="javascript" type="text/javascript">
<!--
$(document).ready(function() {
	//Define enter key for filter submenu input elements
	$('#sm_donations_filter input, #sm_donations_filter select').keypress(function(event) {
		if(event.keyCode == '13') {
			$('#submit_donations_filter').click();
			event.preventDefault();
		}
	});

	//Define enter key for itemlist submenu input elements
	$('#sm_donations_itemlist_accounts input').keypress(function(event) {
		if(event.keyCode == '13') {
			$('#save_itemlist').click();
			event.preventDefault();
		}
	});

	//Donation stats: Show/Hide rows per source for each account
	$('.donations-stats-account').click(function() {
		temp = $(this).attr('id').split('_');
		id = temp[2];
		$('.source_account_'+id).toggle();
	});

	// load richtexteditor (CKEDITOR)
	if(kOOL.module == 'donations') {
		$('.richtexteditor').ckeditor({customConfig : '/donations/inc/ckeditor_custom_config.js' });
	}
});



function donation_recurring(date, amount) {
	d = prompt('<?php print getLL('kota_ko_donations_date'); ?>', date);
	if(d == null) return false;

	a = prompt('<?php print getLL('kota_ko_donations_amount'); ?>', amount);
	if(a == null) return false;

	set_hidden_value('recurring_date', d);
	set_hidden_value('recurring_amount', a);
}
-->
</script>
