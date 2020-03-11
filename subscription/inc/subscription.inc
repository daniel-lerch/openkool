<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2020 Renzo Lauper (renzo@churchtool.org)
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


require_once($BASE_PATH."inc/class.kOOL_listview.php");
require_once("FormException.php");

function ko_subscription_form_list() {
	global $access;

	if($access['subscription']['MAX'] < 1) return;

	//Add KOTA filter
	$where = kota_apply_filter('ko_subscription_forms');

	if($access['subscription']['ALL'] < 2) {
		$groupedAccess = array();
		foreach($access['subscription'] as $formGroupId => $a) {
			if(is_numeric($formGroupId)) {
				$groupedAccess[$a][] = $formGroupId;
			}
		}
		$filter = array();
		if($access['subscription']['ALL'] == 1) {
			$filter[] = 'cruser = '.$_SESSION['ses_userid'];
		} else if(isset($groupedAccess[1])) {
			$filter[] = '(cruser = '.$_SESSION['ses_userid'].' AND form_group IN('.implode(',',$groupedAccess[1]).'))';
		}
		if(isset($groupedAccess[2])) {
			$filter[] = 'form_group IN('.implode(',',$groupedAccess[2]).')';
		}
		if($filter) {
			if($where) {
				$where .= ' AND ';
			}
			$where .= '('.implode(' OR ',$filter).')';
		}
	}

	$countWhere = $where != '' ? 'AND '.$where : '';
	$rows = db_get_count('ko_subscription_forms', 'id', $countWhere);

	$z_limit = 'LIMIT ' . ($_SESSION['show_start']-1) . ', ' . $_SESSION['show_limit'];

	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}

	$sortCol = $_SESSION['sort_forms'];
	if(!$sortCol) $sortCol = 'title';
	$sortOrder = $_SESSION['sort_forms_order'];
	if(!$sortOrder) $sortOrder = 'ASC';

	if($where != '') $where = 'WHERE '.$where;
	$es = db_select_data('ko_subscription_forms', $where, '*', 'ORDER BY '.$sortCol.' '.$sortOrder, $z_limit);

	$list = new kOOL_listview();

	$list->init('subscription', 'ko_subscription_forms', array('edit', 'delete'), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("subscription_forms_list_title"));
	$list->setActions([
		'edit' => ['action' => 'edit_form'],
		'delete' => ['action' => 'delete_form', 'confirm' => TRUE]
	]);
	if ($access['subscription']['ALL'] > 1) $list->setActionNew('new_form');
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_forms'], $_SESSION['sort_forms_order']);
	$list->disableMultiedit();
	$list->setStats($rows);
	$list->setWarning(kota_filter_get_warntext('ko_subscription_forms'));

	$list->render($es);
}

function ko_subscription_formular_form($mode,$id = null) {
	if($mode == "new") {
		$id = 0;
	} else if($mode == "edit") {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data["title"] =  $mode == "new" ? getLL("subscription_new_form_title") : getLL("subscription_edit_form_title");
	$form_data["submit_value"] = getLL("save");
	$form_data["action"] = $mode == "new" ? "submit_new_form" : "submit_edit_form";
	$form_data["cancel"] = "list_forms";
	if($mode == 'edit') {
		$form_data['action_as_new'] = 'submit_new_form';
	}

	ko_multiedit_formular("ko_subscription_forms", "", $id, "", $form_data);

	// show js error if double-opt-in is enabled, but there's no email field
	?>
		<script>
			$(document).ready(function() {
				$('.main > form').submit(function(e) {
					if(this.action.value == 'submit_new_form' || this.action.value == 'submit_edit_form') {
						var doiInput = null;
						for(i = 0; i < this.elements.length; i++) {
							if(this.elements[i].name.startsWith('koi[ko_subscription_forms][double_opt_in]')) {
								doiInput = this.elements[i];
								break;
							}
						}
						var id = doiInput.name.slice(42,doiInput.name.indexOf(']',42));
						if($(doiInput).prop('checked')) {
							var e = $(this.elements.namedItem('koi[ko_subscription_forms][fields]['+id+'][email][mandatory]'));
							if(e.length == 0 || e.prop('checked') == false) {
								alert('<?= getLL('subscription_kota_error_double_opt_in_without_email') ?>');
								return false;
							}
						}
					}
				});
			});
		</script>
	<?php
}


function ko_subscription_form_group_list() {
	global $access;

	if($access['subscription']['MAX'] < 1) return;

	//Add KOTA filter
	$where = kota_apply_filter('ko_subscription_form_groups');

	if($where != '') {
		$count_where = 'AND '.$where;
		$where = 'WHERE '.$where;
	}

	$rows = db_get_count('ko_subscription_form_groups', 'id', $count_where);

	$_SESSION['show_start'] = 1;

	$es = db_select_data('ko_subscription_form_groups', $where, '*', 'ORDER BY `name` ASC');

	$list = new kOOL_listview();
	$list->init('subscription', 'ko_subscription_form_groups', array('log', 'edit', 'delete'), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("subscription_form_groups_list_title"));
	$list->setActions(array('edit' => array('action' => 'edit_form_group'),
			'delete' => array('action' => 'delete_form_group', 'confirm' => TRUE))
	);
	if ($access['subscription']['ALL'] > 1) $list->setActionNew('new_form_group');
	$list->setStats($rows);
	$list->disableMultiedit();
	$list->setWarning(kota_filter_get_warntext('ko_subscription_form_groups'));

	$list->render($es);
}

function ko_subscription_formular_form_group($mode,$id = null) {
	if($mode == "new") {
		$id = 0;
	} else if($mode == "edit") {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data["title"] =  $mode == "new" ? getLL("subscription_new_form_group_title") : getLL("subscription_edit_form_group_title");
	$form_data["submit_value"] = getLL("save");
	$form_data["action"] = $mode == "new" ? "submit_new_form_group" : "submit_edit_form_group";
	$form_data["cancel"] = "list_form_groups";

	ko_multiedit_formular("ko_subscription_form_groups", "", $id, "", $form_data);
}


function ko_subscription_generate_key($formId,$leuteId,$groupId,$action,$ttl = false) {
	if($groupId && !preg_match('/^g([0-9]{6})(?::r([0-9]{6}))?$/',$groupId,$splitGroupId)) {
		throw new \kOOL\Subscription\FormException('group_invalid');
	}
	$values = array(
		$leuteId,
		isset($splitGroupId[1]) ? (int)$splitGroupId[1] : '',
		isset($splitGroupId[2]) ? (int)$splitGroupId[2] : '',
	);
	if($ttl) {
		$values[] = $ttl+time()-1522000000;
	}
	$v = base_convert(implode('a',$values),11,36);
	$r = rand(0,35);
	for($i = 0; $i < strlen($v); $i++) {
		$v[$i] = base_convert((base_convert($v[$i],36,10)+$r)%36,10,36);
	}
	$hash = substr(base_convert(md5($r.$formId.implode($values).KOOL_ENCRYPTION_KEY.$action),16,36),0,8);
	return base_convert($r,10,36).$v.$hash;
}

function ko_subscription_validate_key($key,$formId,&$leuteId,&$groupId,&$error,$action) {
	$r = base_convert($key[0],36,10);
	$v = substr($key,1,-8);
	for($i = 0; $i < strlen($v); $i++) {
		$v[$i] = base_convert((base_convert($v[$i],36,10)+36-$r)%36,10,36);
	}
	$values = explode('a',base_convert($v,36,11));
	if(substr(base_convert(md5($r.$formId.implode($values).KOOL_ENCRYPTION_KEY.$action),16,36),0,8) != substr($key,-8)) {
		$error = 'invalid';
		return false;
	}
	if(isset($values[3]) && $values[3] < time()-1522000000) {
		$error = 'timeout';
		return false;
	}
	$leuteId = $values[0];
	if($values[1] === '') {
		$groupId = null;
	} else {
		$groupId = 'g'.zerofill($values[1],6);
		if($values[2]) {
			$groupId .= ':r'.zerofill($values[2],6);
		}
	}
	$error = false;
	return true;
}

function ko_subscription_create_edit_link($personId,$groupId,$form,$ttl) {
	global $BASE_URL;

	$key = ko_subscription_generate_key($form['id'],$personId,$groupId,'edit_link',$ttl);
	$url = $BASE_URL;
	$url .= 'form/'.$form['url_segment'].'/edit/'.$key;
	return $url;
}

function ko_subscription_send_edit_link($email,$groupId,$form,$ttl) {
	global $MAIL_TRANSPORT;
	if(!check_email($email)) {
		throw new \kOOL\Subscription\FormException('invalid_email');
	}
	$persons = db_select_data('ko_leute',"WHERE email='".$email."' AND deleted=0",'id,groups,vorname,nachname','ORDER BY crdate');
	if(!empty($form['groups'])) {
		if(empty($groupId)) {
			throw new \kOOL\Subscription\FormException('group_missing');
		}
		$fullGid = ko_groups_decode(format_userinput($groupId,'group_role'),'full_gid');
		$persons = array_filter($persons,function($person) use($fullGid) {
			return in_array($fullGid,explode(',',$person['groups']));
		});
		$mod = db_select_data('ko_leute_mod',"WHERE email='".$email."' AND _group_id='".$groupId."'",'vorname,nachname');
	} else {
		$mod = [];
	}
	if($persons) {
		$message = getLL('ko_subscription_send_link_body');
		$names = [];
		foreach($persons as $person) {
			$name = $person['vorname'].' '.$person['nachname'];
			if(!in_array($name,$names)) {
				$names[] = $name;
				$message .= "\n\n".$name."\n";
				$message .= ko_subscription_create_edit_link($person['id'],$groupId,$form,$ttl);
			}
		}
		foreach($mod as $person) {
			$name = $person['vorname'].' '.$person['nachname'];
			if(!in_array($name,$names)) {
				$message .= "\n\n".$name."\n".getLL('ko_subscription_send_link_mod');
				$names[] = $name;
			}
		}
	} else {
		if($mod) {
			$message = getLL('ko_subscription_send_no_link_body_mod');
		} else {
			$message = getLL('ko_subscription_send_no_link_body');
		}
	}
	ko_send_mail(
		ko_subscription_get_sender(),
		$email,
		getLL('ko_subscription_send_link_subject'),
		$message."\n\n".getLL('ko_subscription_send_link_abuse_hint')
	);
	ko_log('subscription_send_edit_link',"an edit link was ordered and sent to: ".$email.", form: ".$form['id'].", group: ".$groupId.", leute_id: ".$person['id'].", expires: ".($ttl ? date('r',time()+$ttl) : 'never'));
}

/**
 * @param array $data
 * @return array
 * @throws kOOL\Subscription\FormException
 */
function ko_subscription_validate_group_data($data) {
	// collect all groups
	$groups = [];
	if(!empty($data['_group_id'])) {
		$groups[$data['_group_id']] = true;
	}
	if(!empty($data['_additional_group_ids'])) {
		$groups = array_merge($groups,$data['_additional_group_ids']);
	}

	// decode and check group and role ids
	foreach($groups as $id => &$group) {
		if(!preg_match('/^g([0-9]{6})(?::r([0-9]{6}))?$/',$id,$matches)) {
			throw new \kOOL\Subscription\FormException('group_invalid');
		}
		$group = [
			'subscribe' => (boolean)$group,
			'fullId' => $id,
			'groupId' => $matches[1],
		];
		if(isset($matches[2])) {
			$group['roleId'] = $matches[2];
		}
	}

	// fetch all group rows
	if($groups) {
		$groupRows = db_select_data('ko_groups','WHERE id IN ('.implode(',',array_column($groups,'groupId')).')');
		foreach($groups as &$group) {
			if(!isset($groupRows[$group['groupId']])) {
				throw new \kOOL\Subscription\FormException('group_invalid');
			}
			$group['row'] = $groupRows[$group['groupId']];
			$group['datafields'] = array_filter(explode(',',$group['row']['datafields']));
			$group['roles'] = array_filter(explode(',',$group['row']['roles']));
		}
	}
	unset($group);

	// check if roles exist for groups
	foreach($groups as $group) {
		if(isset($group['roleId']) && !in_array($group['roleId'],$group['roles'])) {
			throw new \kOOL\Subscription\FormException('role_invalid');
		}
	}

	// check if datafields exist for groups
	if(isset($data['_group_datafields'])) {
		if(!isset($data['_group_id'])) {
			throw new \kOOL\Subscription\FormException('invalid_datafields');
		}
		foreach($data['_group_datafields'] as $dfid => $value) {
			if(!in_array($dfid,$groups[$data['_group_id']]['datafields'])) {
				throw new \kOOL\Subscription\FormException('invalid_datafields');
			}
		}
		$groups[$data['_group_id']]['datafieldData'] = $data['_group_datafields'];
	}
	if(isset($data['_additional_group_datafields'])) {
		if(!isset($data['_additional_group_ids'])) {
			throw new \kOOL\Subscription\FormException('invalid_datafields');
		}
		$groupsById = array_column($groups,null,'groupId');
		foreach($data['_additional_group_datafields'] as $groupId => $datafields) {
			if(!isset($groupsById[$groupId])
				|| !$groupsById[$groupId]['subscribe']
				|| !empty(array_diff(array_keys($datafields),$groupsById[$groupId]['datafields']))
			) {
				throw new \kOOL\Subscription\FormException('invalid_datafields');
			}
			$groups[$groupsById[$groupId]['fullId']]['datafieldData'] = $datafields;
		}
	}

	return $groups;
}

/**
 * @param array $data
 * @return array
 * @throws kOOL\Subscription\FormException
 */
function ko_subscription_validate_leute_data(&$data) {
	// check if fields exist on ko_leute
	$columns = array_column(db_get_columns("ko_leute"),'Field');
	foreach($data as $field => $value) {
		if(!in_array($field,$columns) && !in_array($field,[
			'_crdate',
			'_group_id',
			'_group_datafields',
			'_additional_group_ids',
			'_additional_group_datafields',
			'_bemerkung',
		])) {
			throw new kOOL\Subscription\FormException('fields_not_allowed');
		}
	}
}

/**
 * @param array $data
 * @param int $leuteId
 * @throws kOOL\Subscription\FormException
 * @return int|boolean returns the group id if this was a new subscription, otherwise false
 */
function ko_subscription_store_edit($data,$leuteId) {
	$leuteRow = db_select_data('ko_leute','WHERE id='.$leuteId,'*','','',true);
	if(!$leuteRow) {
		throw new kOOL\Subscription\FormException('invalid_leute_id');
	}

	// ajust anrede and geschlecht if only one of then is set
	if(empty($leuteRow['anrede']) && empty($data['anrede']) && !empty($data['geschlecht'])) {
		if($data['geschlecht'] == 'w') $data['anrede'] = 'Frau';
		if($data['geschlecht'] == 'm') $data['anrede'] = 'Herr';
	}
	if(empty($leuteRow['geschlecht']) && empty($data['geschlecht']) && !empty($data['anrede'])) {
		if($data['anrede'] == 'Herr') $data['geschlecht'] = 'm';
		if($data['anrede'] == 'Frau') $data['geschlecht'] = 'w';
	}

	$groups = ko_subscription_validate_group_data($data);
	ko_subscription_validate_leute_data($data);

	$currentGroups = array_filter(explode(',',$leuteRow['groups']));

	// check for subscriptions and unsubscriptions
	foreach($groups as $id => $group) {
		$fullGid = ko_groups_decode($id,'full_gid');
		$isMember = in_array($fullGid,$currentGroups);
		$groups[$id]['unsubscribe'] = !$group['subscribe'] && $isMember;
		$groups[$id]['subscribe'] = $group['subscribe'] && !$isMember;
	}

	// check if groups are full
	foreach($groups as $group) {
		if($group['subscribe'] && $group['row']['deadline'] != '0000-00-00' && $group['row']['deadline'] < date('Y-m-d')) {
			throw new \kOOL\Subscription\FormException('deadline_expired');
		} else if($group['subscribe']
			&& $group['row']['maxcount']
			&& (!$group['row']['count_role'] || (isset($group['roleId']) && $group['row']['count_role'] == $group['roleId']))
			&& $group['row']['count'] >= $group['row']['maxcount']
		) {
			throw new \kOOL\Subscription\FormException('group_full');
		}
	}

	// store leute data in ko_leute_mod
	$insert = [
		'_leute_id' => $leuteId,
		'_crdate' => date("Y-m-d H:i:s"),
		'_crdate' => isset($data['_crdate']) ? $data['_crdate'] : date("Y-m-d H:i:s"),
	];
	if(isset($data['_bemerkung'])) {
		$insert['_bemerkung'] = $data['_bemerkung'];
	}
	$columns = array_column(db_get_columns("ko_leute"),'Field');
	$change = false;
	foreach($columns as $field) {
		if($field[0] != '_') {
			if(isset($data[$field])) {
				$insert[$field] = $data[$field];
				if($data[$field] != $leuteRow[$field]) {
					$change = true;
				}
			} else {
				$insert[$field] = $leuteRow[$field];
			}
			$insert[$field] = isset($data[$field]) ? trim($data[$field]) : $leuteRow[$field];
		}
	}
	if($change) {
		db_insert_data('ko_leute_mod',$insert);
		ko_log_diff('aa_create',$insert);
	}

	// apply changes to group subscriptions
	$updateGroupCount = [];
	foreach($groups as $id => $group) {
		if($group['subscribe']) {
			$currentGroups[] = $fullGid;
		}
		if($group['unsubscribe']) {
			$currentGroups = array_diff($currentGroups,$existing);
		}
		if(($group['subscribe'] || $group['unsubscribe'])
			&& $group['row']['maxcount']
			&& (!$group['row']['count_role'] || (isset($group['roleId']) && $group['row']['count_role'] == $group['roleId']))
		) {
			$updateGroupCount[] = $group;
		}
	}
	ko_save_leute_changes($leuteId);
	$insert['groups'] = implode(',',$currentGroups);
	db_update_data('ko_leute','WHERE id='.$leuteId,['groups' => $insert['groups']]);
	ko_log_diff('edit_person',$insert,$leuteRow);

	// update group count
	foreach($updateGroupCount as $group) {
		ko_update_group_count($group['groupId'],isset($group['roleId']) ? $group['roleId'] : '');
	}

	// store group datafields
	foreach($groups as $group) {
		if(isset($group['datafieldData'])) {
			$current = db_select_data('ko_groups_datafields_data','WHERE group_id='.$group['groupId'].' AND person_id='.$leuteId.' AND deleted=0');
			$current = array_column($current,null,'datafield_id');
			foreach($group['datafieldData'] as $datafieldId => $value) {
				if(isset($current[$datafieldId])) {
					db_update_data('ko_groups_datafields_data','WHERE id='.$current[$datafieldId]['id'],['value' => $value]);
				} else {
					db_insert_data('ko_groups_datafields_data',[
						'datafield_id' => $datafieldId,
						'group_id' => $group['groupId'],
						'person_id' => $leuteId,
						'value' => $value,
					]);
				}
			}
		}
	}

	if($groups[$data['_group_id']]['subscribe']) {
		return $data['_group_id'];
	} else {
		return false;
	}
}

/**
 * @param array $data
 * @param boolean $moderated
 * @param boolean $overflow
 * @throws kOOL\Subscription\FormException
 * @return int returns the ko_leute id of the inserted entry if any (or null if $moderated is true)
 */
function ko_subscription_store_subscription($data,$moderated,$overflow) {
	if(!format_userinput($data['_group_id'], 'alphanumlist')) {
		throw new \kOOL\Subscription\FormException('group_missing');
	}

	// ajust anrede and geschlecht if only one of then is set
	if(empty($data['anrede']) && !empty($data['geschlecht'])) {
		if($data['geschlecht'] == 'w') $data['anrede'] = 'Frau';
		if($data['geschlecht'] == 'm') $data['anrede'] = 'Herr';
	}
	if(empty($data['geschlecht']) && !empty($data['anrede'])) {
		if($data['anrede'] == 'Herr') $data['geschlecht'] = 'm';
		if($data['anrede'] == 'Frau') $data['geschlecht'] = 'w';
	}

	$groups = ko_subscription_validate_group_data($data);
	ko_subscription_validate_leute_data($data);

	// check if groups are full or deadline is over
	foreach($groups as $group) {
		if($group['subscribe'] && $group['row']['deadline'] != '0000-00-00' && $group['row']['deadline'] < date('Y-m-d')) {
			throw new \kOOL\Subscription\FormException('deadline_expired');
		} else if($group['subscribe']
			&& $group['row']['maxcount']
			&& (!$group['row']['count_role'] || (isset($group['roleId']) && $group['row']['count_role'] == $group['roleId']))
			&& $group['row']['count'] >= $group['row']['maxcount']
		) {
			if($overflow) {
				// force moderation if group overflows
				$moderated = true;
			} else {
				throw new \kOOL\Subscription\FormException('group_full');
			}
		}
	}

	if($moderated) {
		// store in ko_leute_mod
		$insert = [
			'_crdate' => date("Y-m-d H:i:s"),
		];
		foreach($data as $field => $value) {
			if(in_array($field,['_group_datafields','_additional_group_ids','_additional_group_datafields'])) {
				$insert[$field] = serialize($value);
			} else {
				$insert[$field] = trim($value);
			}
		}
		db_insert_data('ko_leute_mod',$insert);
		ko_log_diff('aa_create',$insert);

		$leuteId = null;
	} else {

		// apply changes to group subscriptions
		$currentGroups = [];
		$updateGroupCount = [];
		foreach($groups as $id => $group) {
			if($group['subscribe']) {
				$currentGroups[] = ko_groups_decode($id,'full_gid');
				if($group['row']['maxcount']
					&& (!$group['row']['count_role'] || (isset($group['roleId']) && $group['row']['count_role'] == $group['roleId']))
				) {
					$updateGroupCount[] = $group;
				}
			}
		}

		// store in ko_leute and ko_leute_revisions
		$insert = [];
		$insert['groups'] = implode(',',$currentGroups);
		$insert['crdate'] = $insert['lastchange'] = date("Y-m-d H:i:s");
		foreach($data as $field => $value) {
			if($field[0] != '_') {
				$insert[$field] = trim($value);
			}
		}
		$leuteId = db_insert_data('ko_leute', $insert);
		ko_log_diff('new_person',array_merge(['id' => $leuteId],$insert));

		//Store group history
		ko_create_groups_snapshot($leuteId);

		// update group count
		foreach($updateGroupCount as $group) {
			ko_update_group_count($group['groupId'],isset($group['roleId']) ? $group['roleId'] : '');
		}

		// store group datafields
		foreach($groups as $group) {
			if(isset($group['datafieldData'])) {
				foreach($group['datafieldData'] as $datafieldId => $value) {
					db_insert_data('ko_groups_datafields_data',[
						'datafield_id' => $datafieldId,
						'group_id' => $group['groupId'],
						'person_id' => $leuteId,
						'value' => $value,
					]);
				}
			}
		}

		// add revision entry
		$revision = [
			'leute_id' => $leuteId,
			'reason' => 'groupsubscription',
			'crdate' => date('Y-m-d H:i:s'),
			'group_id' => $data['_group_id'],
		];
		db_insert_data('ko_leute_revisions',$revision);
		ko_log_diff('new_leute_revision', $revision);
	}

	return $leuteId;
}

function ko_subscription_send_mails($form,$data,$rawData,$leuteId,$mode) {
	global $MAIL_TRANSPORT, $BASE_URL;

	$summary = '<table>';
	foreach($data as $d) {
		$summary .= '<tr><td>'.$d['label'].'</td><td><b>'.$d['value'].'</b></td></tr>';
	}
	$summary .= '</table>';

	$visitorName = trim($data['vorname']['value'].' '.$data['nachname']['value']);

	if($form['notification_to']) {
		$notification = $form['notification_body'] ?: '<p>'.getLL('subscription_default_notification_body').'</p>';
		$notification .= $summary;

		$replyTo = empty($data['email']['value']) ? [] : $data['email']['value'];
		if($replyTo && $visitorName) {
			$replyTo = [$replyTo => $visitorName];
		}

		ko_send_html_mail(
			ko_subscription_get_sender(),
			array_filter(preg_split('#[,;/\s]+#',$form['notification_to'])),
			$form['notification_subject'] ?: getLL('subscription_default_notification_subject'),
			$notification,
			[],[],[],
			$replyTo
		);
	}

	if(!empty($data['email']['value']) && $form['response']) {
		$response = $form['response_body_'.$mode] ?: '<p>'.getLL('subscription_default_'.$mode.'_response_body').'</p>';
		$response = preg_replace_callback('/###([a-zA-Z0-9_]*)###/',function($matches) use ($form,$data,$rawData,$leuteId) {
			$key = strtolower($matches[1]);
			if($key == 'edit_link') {
				$url = $BASE_URL.'form/'.$form['url_segment'];
				if($leuteId) {
					$url .= '/edit/'.ko_subscription_generate_key($form['id'],$leuteId,isset($rawData['_group_id']) ? $rawData['_group_id'] : '','edit_link',86400);
				} else if($form['edit_link']) {
					$url .= '/linkform';
				} else {
					return '<i>'.getLL('subscription_no_edit_link').'</i>';
				}
				return '<a href="'.$url.'">'.$url.'</a>';
			}
			if(isset($data[$key])) {
				return $data[$key]['value'];
			}
			if(preg_match('/^salutation_((in)?formal)$/',$key,$m)) {
				return ko_subscription_generate_salutation($rawData,$m[1],$leuteId);
			}
			return $matches[0];
		},$response);
		$response .= $summary;

		ko_send_html_mail(
			ko_subscription_get_sender(),
			$visitorName ? [$data['email']['value'] => $visitorName] : $data['email']['value'],
			$form['response_subject_'.$mode] ?: getLL('subscription_default_'.$mode.'_response_subject'),
			$response,
			[],[],[],
			$form['response_replyto'] ?: []
		);
	}
}

function ko_subscription_send_double_opt_in_mail($form,$data,$rawData) {
	global $MAIL_TRANSPORT, $BASE_URL;

	$row = [
		'data' => json_encode_latin1([
			$rawData,
			$data,
		]),
		'form' => $form['id'],
		'email' => $data['email']['value'],
	];
	$id = db_insert_data('ko_subscription_double_opt_in',$row);

	$url = $BASE_URL.'form/'.$form['url_segment'].'/doubleoptin/'.ko_subscription_generate_key($form['id'],$id,null,'double_opt_in');

	$msg = '<p>'.ko_subscription_generate_salutation($rawData,'formal').'</p>';
	$msg .= '<p>'.sprintf(getLL('ko_subscription_double_opt_in_body'),$BASE_URL).'</p>';
	$msg .= '<p>'.getLL('ko_subscription_send_link_abuse_hint').'</p>';
	$msg .= '<p><a href="'.$url.'" style="display:inline-block; padding:5px 15px; background-color:#2b6ea2; color:#ffffff;"><span style="color:#ffffff;">'.getLL('ko_subscription_double_opt_in_link_text').'</span></a></p>';

	$visitorName = trim($data['vorname']['value'].' '.$data['nachname']['value']);
	ko_send_html_mail(
		ko_subscription_get_sender(),
		$visitorName ? [$data['email']['value'] => $visitorName] : $data['email']['value'],
		getLL('ko_subscription_double_opt_in_subject'),
		$msg,
		[],[],[],
		$form['response_replyto'] ?: []
	);

	db_update_data('ko_subscription_double_opt_in','WHERE id='.$id,['sent_time' => date('Y-m-d H:i:s')]);
	ko_log('subscription_double_opt_in_sent','Double-Opt-In mail sent to '.$data['email']['value']);
}


function ko_subscription_generate_salutation($rawData,$type,$leuteId = null) {
	if((!isset($rawData['geschlecht']) || (empty($rawData['geschlecht']) && !isset($rawData['anrede']))) && $leuteId) {
		$row = db_select_data('ko_leute','WHERE id='.$leuteId,'geschlecht,anrede','','',true);
	}
	$gender = '';
	if(!empty($rawData['geschlecht'])) {
		$gender = $rawData['geschlecht'];
	}
	if(!$gender && !empty($rawData['anrede'])) {
		if($rawData['anrede'] == 'Herr') $gender = 'm';
		if($rawData['anrede'] == 'Frau') $gender = 'w';
	}
	if(!$gender && !empty($row['geschlecht'])) {
		$gender = $row['geschlecht'];
	}
	if(!$gender && !empty($row['anrede'])) {
		if($row['anrede'] == 'Herr') $gender = 'm';
		if($row['anrede'] == 'Frau') $gender = 'w';
	}
	$firstname = !empty($rawData['vorname']) ? $rawData['vorname'] : (!empty($row['vorname']) ? $row['vorname'] : '');
	$lastname = !empty($rawData['nachname']) ? $rawData['nachname'] : (!empty($row['nachname']) ? $row['nachname'] : '');
	$name = $type == 'informal' ? $firstname : $lastname;
	if($gender && $name) {
		return getLL('leute_salutation_'.$type.'_'.$gender.'_unmarried').' '.$name;
	} else {
		return getLL('leute_salutation_'.$type.'_');
	}
}

function ko_subscription_get_log_message($data,$prefix = '') {
	$msg = '';
	foreach($data as $key => $value) {
		if($msg) {
			$msg .= ", ";
		}
		if($prefix) {
			$key = $prefix.'['.$key.']';
		}
		if(is_array($value)) {
			if(empty($value)) {
				$msg .= $key.' -> []';
			} else {
				$msg .= ko_subscription_get_log_message($value,$key);
			}
		} else {
			$msg .= $key.' -> '.(is_string($value) ? $value : var_export($value,true));
		}
	}
	return $msg;
}

function ko_subscription_list_unconfirmed_double_opt_ins() {
	global $access;

	$dois = db_select_data('ko_subscription_double_opt_in doi JOIN ko_subscription_forms f on(doi.form=f.id)',"WHERE doi.status=0",'doi.*,f.form_group,f.cruser,f.title form_title','','',false,true);

	foreach($dois as $i => $doi) {
		$formAccess = max($access['subscription']['ALL'],$access['subscription'][$doi['form_group']]);
		if($formAccess == 1 && $doi['cruser'] != $_SESSION['ses_userid']) {
			$formAccess = 0;
		}

		if($formAccess) {
			list($dois[$i]['rawData'],$dois[$i]['data']) = json_decode_latin1($doi['data']);
			if(preg_match('/^g([0-9]{6})(?::r([0-9]{6}))?$/',$dois[$i]['rawData']['_group_id'],$m)) {
				$dois[$i]['group'] = $m[1];
				if(isset($m[2])) {
					$dois[$i]['role'] = $m[2];
				}
			}
		} else {
			unset($dois[$i]);
		}
	}

	if(empty($dois)) {
		echo '<div class="alert alert-info alert-dismissible" role="alert">';
		echo '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
		echo getLL('subscription_no_double_opt_ins');
		echo '</div>';
		return;
	}

	if($groupIds = array_column($dois,'group')) {
		$groups = db_select_data('ko_groups','WHERE id IN('.implode(',',array_unique($groupIds)).')','id,name');
	}
	if($roleIds = array_column($dois,'role')) {
		$roles = db_select_data('ko_grouproles','WHERE id IN('.implode(',',array_unique($roleIds)).')','id,name');
	}

	$out = '<table class="table">';
	$out .= '<tr>';
	$out .= '<th>'.getLL('subscription_double_opt_in_sent_time').'</th>';
	$out .= '<th>'.getLL('subscription_double_opt_in_email').'</th>';
	$out .= '<th>'.getLL('subscription_double_opt_in_name').'</th>';
	$out .= '<th>'.getLL('subscription_double_opt_in_form_title').'</th>';
	$out .= '<th>'.getLL('subscription_double_opt_in_group').'</th>';
	$out .= '<th></th>';
	$out .= '</tr>';
	foreach($dois as $doi) {
		$out .= '<tr>';
		$out .= '<td>'.date('d.m.Y H:i:s',strtotime($doi['sent_time'])).'</td>';
		$out .= '<td>'.$doi['email'].'</td>';
		$out .= '<td>'.trim($doi['data']['vorname']['value'].' '.$doi['data']['nachname']['value']).'</td>';
		$out .= '<td>'.$doi['form_title'].'</td>';
		$out .= '<td>'.(isset($doi['group']) ? $groups[$doi['group']]['name'] : '').(isset($doi['role']) ? ': '.$roles[$doi['role']]['name'] : '').'</td>';
		$out .= '<td><a href="?action=confirm_double_opt_in&doi='.$doi['id'].'">'.getLL('subscription_confirm_double_opt_in').'</a></td>';
		$out .= '</tr>';
	}
	$out .= '</table>';
	echo $out;
}






function ko_subscription_settings() {
	global $smarty;
	global $access, $MODULES;

	if($access['subscription']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');
	$frmgroup[$gc]['tab'] = true;

	$value = ko_get_userpref($_SESSION['ses_userid'], 'show_limit_forms');
	if(!$value) $value = 20;
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('subscription_settings_limit_forms'),
		'type' => 'text',
		'name' => 'txt_limit_forms',
		'value' => ko_html($value),
	);


	//Global settings
	if($access['subscription']['MAX'] > 1) {
		$gc++;
		$rowcounter = 0;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');
		$frmgroup[$gc]['tab'] = true;

		$value = ko_html(ko_get_setting('subscription_sender_email'));
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('subscription_settings_sender_email'),
			'type' => 'text',
			'name' => 'txt_sender_email',
			'value' => $value,
		);
		$value = ko_html(ko_get_setting('subscription_sender_name'));
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('subscription_settings_sender_name'),
			'type' => 'text',
			'name' => 'txt_sender_name',
			'value' => $value,
		);

		$value = ko_html(ko_get_setting('subscription_text_header'));
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('subscription_settings_text_header'),
			'type' => 'richtexteditor',
			'name' => 'txt_header',
			'value' => $value,
		);
		$value = ko_html(ko_get_setting('subscription_text_footer'));
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('subscription_settings_text_footer'),
			'type' => 'richtexteditor',
			'name' => 'txt_footer',
			'value' => $value,
		);
	}



	//Allow plugins to add further settings
	hook_form('subscription_settings', $frmgroup, '', '');

	//display the form
	$smarty->assign('tpl_titel', getLL('subscription_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_subscription_settings');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_subscription');
	if(!$cancel) $cancel = 'list_forms';
	$smarty->assign('tpl_cancel', $cancel);
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->assign('help', ko_get_help('subscription', 'subscription_settings'));

	$smarty->display('ko_formular.tpl');
}//ko_subscription_settings()




function ko_subscription_get_sender() {
	$email = ko_get_setting('subscription_sender_email');
	$name = ko_get_setting('subscription_sender_name');

	if(!check_email($email)) return '';
	if(!$name) $name = ko_get_setting('info_name');

	return array($email => $name);
}
