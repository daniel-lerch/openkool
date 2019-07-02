<?php
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

error_reporting(E_ALL);

//Only allow call from cli
if(!isset($argc) || $argc < 1) exit;

//Get ko_path from server settings
$ko_path = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
if(substr($ko_path, -1) != '/') $ko_path .= '/';

if(isset($_POST['GLOBALS']) || isset($_GET['GLOBALS'])) die('You cannot set the GLOBALS-array from outside this script.');


$ko_menu_akt = 'scheduler';
require_once($ko_path.'inc/ko.inc');

//Log in as _scheduler user, fall back to root user to have enough access
$slogin = db_select_data('ko_admin', "WHERE `login` = '_scheduler'", '*', '', '', TRUE);
if(!$slogin['id']) {
	$slogin = array('id' => ko_get_root_id(), 'login' => 'root');
}
$_SESSION['ses_username'] = $slogin['login'];
$_SESSION['ses_userid'] = $slogin['id'];


//Include tasks from plugins
$my_tasks = hook_include_scheduler_task();
foreach($my_tasks as $mt) {
	include_once($mt);
}


//Get tasks from DB
$tasks = db_select_data('ko_scheduler_tasks', "WHERE `status` = '1' AND `next_call` <= NOW()");
if(sizeof($tasks) > 0) {
	require_once($ko_path.'inc/cron.php');

	foreach($tasks as $task) {
		//Call task's function
		if(function_exists($task['call'])) call_user_func($task['call']);

		//Set next_call
		$log_message = '';
		try {
			$cron = Cron\CronExpression::factory($task['crontime']);
			$next_call = $cron->getNextRunDate()->format('Y-m-d H:i:s');
			$status = 1;
			//$log_message = 'Task '.$task['name'].' ('.$task['id'].'): '.$task['call'].', next_call: '.$next_call;
		} catch (Exception $e) {
			$status = 0;
			$next_call = '0000-00-00 00:00:00';
			$log_message = 'ERROR: '.$e->getMessage().': Task deactivated';
		}
		db_update_data('ko_scheduler_tasks', "WHERE `id` = '".$task['id']."'", array('next_call' => $next_call,
																																								 'last_call' => date('Y-m-d H:i:s'),
																																								 'status' => $status)
		);

		//Log entry
		if($log_message) ko_log('scheduler', $log_message);

	}//foreach(tasks as task)

}//if(sizeof(tasks))

