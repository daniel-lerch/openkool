<?php

error_reporting(0);

session_start();

$ko_path = '../';
$ko_menu_akt = 'upload';

ob_start();
require_once($ko_path . 'inc/ko.inc');
$loginError = ob_get_contents();
ob_end_clean();

if($loginError) {
	session_destroy();
	$retVal = array('success' => false, 'error' => $loginError, 'preventRetry' => true);
} else {
	$storeFolder = $BASE_PATH.'my_images/temp/';

	if (!empty($_FILES)) {

		$uid = $_POST['qquuid'];
		$origName = preg_replace("/[^a-z0-9 \_\-\.]/i", '', $_FILES['qqfile']['name']);
		$tempFile = $_FILES['qqfile']['tmp_name'];

		$targetFile =  $storeFolder.$uid.'_'.$origName;

		ko_log('upload_file', "uploaded file: '{$uid}_{$origName}'");
		move_uploaded_file($tempFile,$targetFile);

		$thumbnailFile = '';
		if (@is_array($imageSize = getimagesize($targetFile))) {
			$thumbnailFile = $targetFile.'.thumbnail';
			copy($targetFile, $thumbnailFile);
			ko_pic_scale_image($thumbnailFile, 120);
		}

		$retVal = array('success' => true, 'newUuid' => $uid.'_'.$origName);
	} else if ($_POST['_method'] == 'DELETE') {
		$uid = $_POST['qquuid'];
		if (!$uid) {
			http_response_code(403);
			exit;
		} else {
			$found = FALSE;
			if ($handle = opendir($storeFolder)) {
				while (false !== ($file = readdir($handle))) {
					if (substr($file, 0, strlen($uid)) == $uid) {
						$found = TRUE;
						unlink($storeFolder . $file);
						ko_log('delete_file', "deleted file: '{$file}'");
					}
				}
				closedir($handle);
			}
			if (!$found) {
				http_response_code(403);
				exit;
			}
		}
	} else {
		$retVal = array('success' => false);
	}
}


array_walk_recursive($retVal, 'utf8_encode_array');
print json_encode($retVal);
?>