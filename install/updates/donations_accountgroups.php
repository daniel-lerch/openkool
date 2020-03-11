<?php

/*
 * Update script to get all values from ko_donations_accounts.account_group and create new
 * entries for ko_donations_accountgroups
 */


$UPDATES_CONFIG['donations_accountgroups'] = array(
	'name' => 'donations_accountgroups',
	'description' => "Create db entries for ko_donations_accountgroups from ko_donations_accounts.account_group.",
	'crdate' => '2019-11-13',
	'version' => 'R48',
	'optional' => '0',
	'module' => 'donations',
);




/*
 * Main update function
 *
 * @return mixed: int 0 on success, error message as string otherwise
 */
function ko_update_donations_accountgroups() {
	$new_groups = array();

	$where = "WHERE `account_group` != '' AND `accountgroup_id` = '0'";
	$accounts = db_select_data('ko_donations_accounts', $where);
	foreach($accounts as $account) {
		$new_group_id = $new_groups[$account['account_group']];
		if(!is_numeric($new_group_id)) {
			$data = ['title' => $account['account_group'], 'crdate' => date('Y-m-d H:i:s'), 'cruser' => $_SESSION['ses_userid']];
			$new_group_id = db_insert_data('ko_donations_accountgroups', $data);
			$new_groups[$account['account_group']] = $new_group_id;
		}

		$where = "WHERE `id` = '".$account['id']."'";
		$data = ['accountgroup_id' => $new_group_id];
		db_update_data('ko_donations_accounts', $where, $data);
	}

	//All OK
	return 0;
}//ko_update_donations_accountgroups()
