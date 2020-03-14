<script language="javascript" type="text/javascript">
	<!--
	function Account(hasGroup) {
		this.hasGroup = hasGroup
	}

	function selAccount(aid, element) {

		var accounts = {};
		<?php

		$accounts = db_select_data("ko_donations_accounts", "WHERE 1=1", "*");

		//Build code for event groups with their data
		$code = "";
		$code .= sprintf("accounts['%s'] = new Account(%s);\n",
											 '',
											 'false'
											 );
		foreach($accounts as $account) {
			$code .= sprintf("accounts['%s'] = new Account(%s);\n",
											 $account["id"],
											 ($account['group_id'] ? 'true' : 'false')
											 );
		}
		print $code;
		?>

		var add_to_group = $(element).closest('.donations-mod-entry').find("[id*='add_to_group']").parent();

		if(accounts[aid]) {
			if(accounts[aid].hasGroup) {
				add_to_group.show();
			}
			else {
				add_to_group.hide();
			}
		}
	}

	$(function() {
		$('[name*="account"]').each(function(e) {
			selAccount(this.value, this);
		})

	});

	-->
</script>
