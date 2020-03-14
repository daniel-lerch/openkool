<?php

require_once($BASE_PATH.'inc/class.kOOL_listview.php');

function ko_list_crm_projects($output=TRUE) {
	global $access;

	//Set filters from KOTA
	$z_where = '';
	$kota_where = kota_apply_filter('ko_crm_projects');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	if (!$_SESSION['sort_crm_projects']) $_SESSION['sort_crm_projects'] = '`title`';
	if (!$_SESSION['sort_crm_projects_order']) $_SESSION['sort_crm_projects_order'] = 'ASC';
	$order = 'ORDER BY '.$_SESSION['sort_crm_projects'].' '.$_SESSION['sort_crm_projects_order'];

	$rows = db_get_count('ko_crm_projects', 'id', $z_where);
	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
	$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	ko_get_crm_projects($es, $z_where, $z_limit, $order);

	//Check for access, otherwise don't show
	foreach($es as $did => $d) {
		if($access['crm']['ALL'] < 5 && $access['crm'][$did] < 5) {
			unset($es[$did]);
			$rows--;
		}
	}

	//Manual access for deletion
	$manual_access = array();
	foreach($es as $k => $v) {
		if(($access['crm']['ALL'] > 4 || $access['crm'][$k] > 4)) {
			$manual_access['delete'][$k] = TRUE;
			$manual_access['edit'][$k] = TRUE;
		} else {
			$manual_access['delete'][$k] = FALSE;
			$manual_access['edit'][$k] = FALSE;
		}
	}

	$list = new kOOL_listview();

	$list->init('crm', 'ko_crm_projects', array('chk', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('crm_projects_list_title'));
	$list->setAccessRights(array('edit' => 5, 'delete' => 5), $access['crm']);
	$list->setActions(array('edit' => array('action' => 'edit_project'),
			'delete' => array('action' => 'delete_project', 'confirm' => TRUE))
	);
	if ($access['crm']['MAX'] > 4) $list->setActionNew('new_project');
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_crm_projects'], $_SESSION['sort_crm_projects_order']);
	$list->setStats($rows);
	$list->setWarning(kota_filter_get_warntext('ko_crm_projects'));


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_crm_projects()



function ko_list_crm_status($output=TRUE) {
	global $access;

	//Set filters from KOTA
	$z_where = '';
	$kota_where = kota_apply_filter('ko_crm_status');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	if (!$_SESSION['sort_crm_status']) $_SESSION['sort_crm_status'] = '`title`';
	if (!$_SESSION['sort_crm_status_order']) $_SESSION['sort_crm_status_order'] = 'ASC';
	$order = 'ORDER BY '.$_SESSION['sort_crm_status'].' '.$_SESSION['sort_crm_status_order'];

	$rows = db_get_count('ko_crm_status', 'id', $z_where);
	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
	$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	ko_get_crm_status($es, $z_where, $z_limit, $order);

	//Check for access, otherwise don't show
	foreach($es as $did => $d) {
		if($access['crm']['ALL'] < 5 && $access['crm'][$did] < 5) {
			unset($es[$did]);
			$rows--;
		}
	}

	//Manual access for deletion
	$manual_access = array();
	foreach($es as $k => $v) {
		if(($access['crm']['ALL'] > 4 || $access['crm'][$k] > 4)) {
			$manual_access['delete'][$k] = TRUE;
			$manual_access['edit'][$k] = TRUE;
		} else {
			$manual_access['delete'][$k] = FALSE;
			$manual_access['edit'][$k] = FALSE;
		}
	}

	$list = new kOOL_listview();

	$list->init('crm', 'ko_crm_status', array('chk', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('crm_status_list_title'));
	$list->setAccessRights(array('edit' => 5, 'delete' => 5), $access['crm']);
	$list->setActions(array('edit' => array('action' => 'edit_status'),
			'delete' => array('action' => 'delete_status', 'confirm' => TRUE))
	);
	if ($access['crm']['MAX'] > 4) $list->setActionNew('new_status');
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_crm_status'], $_SESSION['sort_crm_status_order']);
	$list->setStats($rows);
	$list->setWarning(kota_filter_get_warntext('ko_crm_status'));


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_crm_status()



function ko_list_crm_contacts($output=TRUE) {
	global $access;

	//Set filters from KOTA
	apply_crm_projects_filter($z_where);
	$kota_where = kota_apply_filter('ko_crm_contacts');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	if (!$_SESSION['sort_crm_contacts']) $_SESSION['sort_crm_contacts'] = '`date`';
	if (!$_SESSION['sort_crm_contacts_order']) $_SESSION['sort_crm_contacts_order'] = 'ASC';
	$order = 'ORDER BY '.$_SESSION['sort_crm_contacts'].' '.$_SESSION['sort_crm_contacts_order'];

	$rows = db_get_count('ko_crm_contacts', 'id', $z_where);
	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
	$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	ko_get_crm_contacts($es, $z_where, $z_limit, $order);

	//Check for access, otherwise don't show
	foreach($es as $cid => $contact) {
		if(!ko_get_crm_contacts_access($contact, 'view')) {
			unset($es[$cid]);
			$rows--;
		}
	}

	//Manual access for deletion
	$manual_access = array();
	foreach($es as $k => $v) {
		if(ko_get_crm_contacts_access($contact, 'edit')) {
			$manual_access['delete'][$k] = TRUE;
			$manual_access['edit'][$k] = TRUE;
		} else {
			$manual_access['delete'][$k] = FALSE;
			$manual_access['edit'][$k] = FALSE;
		}
	}

	$list = new kOOL_listview();

	$list->init('crm', 'ko_crm_contacts', array('chk', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('crm_contacts_list_title'));
	$list->setAccessRights(array('edit' => 1, 'delete' => 1), $access['crm']);
	$list->setActions(array('edit' => array('action' => 'edit_contact'),
			'delete' => array('action' => 'delete_contact', 'confirm' => TRUE))
	);
	$list->setActionNew('new_contact');
	$list->setManualAccess('delete', $manual_access['delete']);
	$list->setManualAccess('edit', $manual_access['edit']);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_crm_contacts'], $_SESSION['sort_crm_contacts_order']);
	$list->setStats($rows);
	$list->setWarning(kota_filter_get_warntext('ko_crm_contacts'));


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_crm_contacts()



function ko_formular_crm_project($mode='new', $id=0) {
	global $access;

	if($mode == 'new') {
		if($access['crm']['ALL'] < 5) return FALSE;
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
		if ($access['crm'][$id] < 5 && $access['crm']['ALL'] < 5) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('crm_projects_form_title_new') : getLL('crm_projects_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_project' : 'submit_edit_project';
	if($mode == 'edit' && $access['crm']['ALL'] >= 5) {
		$form_data['action_as_new'] = 'submit_as_new_project';
		$form_data['label_as_new'] = getLL('crm_projects_form_submit_as_new');
	}
	$form_data['cancel'] = 'list_projects';

	ko_multiedit_formular('ko_crm_projects', '', $id, '', $form_data);
}//ko_formular_crm_project()



function ko_formular_crm_status($mode='new', $id=0) {
	global $access;

	if($access['crm']['ALL'] < 5) return FALSE;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('crm_status_form_title_new') : getLL('crm_status_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_status' : 'submit_edit_status';
	if($mode == 'edit' && $access['crm']['ALL'] >= 5) {
		$form_data['action_as_new'] = 'submit_as_new_status';
		$form_data['label_as_new'] = getLL('crm_status_form_submit_as_new');
	}
	$form_data['cancel'] = 'list_status';

	ko_multiedit_formular('ko_crm_status', '', $id, '', $form_data);
}//ko_formular_crm_status()



function ko_formular_crm_contact($mode='new', $id=0) {
	global $access, $KOTA;

	if($mode == 'new') {
		if($access['crm']['MAX'] < 2) return FALSE;
		$id = 0;
		unset($KOTA['ko_crm_contacts']['cruser']['form']);
	} else if($mode == 'edit') {
		if(!$id) return FALSE;

		ko_get_crm_contacts($contact, " AND `id` = '" . $id . "'", '', '', TRUE, TRUE);
		// check access
		if (!ko_get_crm_contacts_access($contact, 'edit')) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('crm_contacts_form_title_new') : getLL('crm_contacts_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_contact' : 'submit_edit_contact';
	if($mode == 'edit' && $access['crm']['ALL'] >= 5) {
		$form_data['action_as_new'] = 'submit_as_new_contact';
		$form_data['label_as_new'] = getLL('crm_contacts_form_submit_as_new');
	}
	$form_data['cancel'] = 'list_contacts';

	ko_multiedit_formular('ko_crm_contacts', '', $id, '', $form_data);
}//ko_formular_crm_contact()



function ko_crm_settings() {
	global $smarty, $access, $BASE_PATH;

	if($access['crm']['MAX'] < 1) return FALSE;

	//Build settings form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');
	$frmgroup[$gc]['tab'] = TRUE;

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('crm_settings_default_view'),
		'type' => 'select',
		'name' => 'sel_default_view',
		'values' => array('list_projects', 'list_contacts'),
		'descs' => array(getLL('submenu_crm_list_projects'), getLL('submenu_crm_list_contacts')),
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_crm'))
	);
	//Limits
	$value = ko_get_userpref($_SESSION['ses_userid'], 'show_limit_crm_contacts');
	if (!$value) $value = 20;
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('crm_settings_limit_contacts'),
		'type' => 'text',
		'name' => 'txt_limit_contacts',
		'value' => ko_html($value),
	);
	$rowcounter++;


	//Global settings
	//Access check
	if($access['crm']['MAX'] > 4) {
		$gc++;
		$rowcounter = 0;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');
		$frmgroup[$gc]['tab'] = TRUE;

		$project_id = ko_get_setting('crm_group_email_project_id');
		$x = kota_get_form('ko_crm_contacts', 'project_id');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('crm_settings_group_email_project_id'),
			'type' => 'select',
			'name' => 'sel_crm_group_email_project_id',
			'value' => $project_id,
			'values' => $x['values'],
			'descs' => $x['descs'],
		);
	}

	hook_form('crm_settings', $frmgroup, '', '');


	//display the form
	$smarty->assign('tpl_titel', getLL('crm_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_crm_settings');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_crm');
	if(!$cancel) $cancel = 'list_contacts';
	$smarty->assign('tpl_cancel', $cancel);
	$smarty->assign('tpl_groups', $frmgroup);

	$smarty->assign('help', ko_get_help('crm', 'crm_settings'));

	$smarty->display('ko_formular.tpl');
}//ko_crm_settings()



function apply_crm_projects_filter(&$z_where) {
	$z_where = '';
	$projectIds = $_SESSION['show_crm_projects'];
	$newProjectIds = array();
	foreach ($projectIds as $projectId) {
		if (trim($projectId) === '') continue;
		if ($projectId === 0) {
			$newProjectIds[] = "";
		}
		else {
			$newProjectIds[] = $projectId;
		}
	}
	if (sizeof($newProjectIds) > 0) {
		$z_where = " AND `project_id` in ('" . implode("','", $newProjectIds) . "') ";
	}
	else {
		$z_where = " AND 1=2 ";
	}
}
