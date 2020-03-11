<?php

/*
 * Update script 
 */


$UPDATES_CONFIG['mcrypt_entries'] = array(
	'name' => 'mcrypt_entries',
	'description' => "Convert encrypted data from mcrypt to openssl (needs max PHP 7.1 to run)",
	'crdate' => '2019-10-03',
	'version' => 'R48',
	'optional' => '0',
	'module' => 'tools',
);




/*
 * Main update function
 *
 * @return mixed: int 0 on success, error message as string otherwise
 */
function ko_update_mcrypt_entries() {
	global $BASE_PATH;

	//Check for PHP version < 7.2
	if(version_compare(phpversion(), '7.2.0', '>=')) return 'PHP version smaller than 7.2 needed for mcrypt';

	//Check for encryption key
	if(!KOOL_ENCRYPTION_KEY) return 'No encryption key set';

	include_once($BASE_PATH.'inc/class.mcrypt.php');
	$cryptM = new mcrypt('aes');
	$cryptM->setKey(KOOL_ENCRYPTION_KEY);

	include_once($BASE_PATH.'inc/class.openssl.php');
	$crypt = new openssl('AES-256-CBC');
	$crypt->setKey(KOOL_ENCRYPTION_KEY);


	//TYPO3 password (from tools module)
	$pwd_enc = ko_get_setting('typo3_pwd');
	if($pwd_enc) {
		$pwd = trim($cryptM->decrypt($pwd_enc));

		$pwdNew = $crypt->encrypt($pwd);
		ko_set_setting('typo3_pwd', $pwdNew);
	}


	//VESR email password (v11)
	$vesrEmail_enc = ko_get_setting('vesr_import_email_pass');
	if($vesrEmail_enc) {
		$vesrEmail = trim($cryptM->decrypt($vesrEmail_enc));

		$vesrNew = $crypt->encrypt($vesrEmail);
		ko_set_setting('vesr_import_email_pass', $vesrNew);
	}


	//All OK
	return 0;
}//ko_update_mcrypt_entries()
