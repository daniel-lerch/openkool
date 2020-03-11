<?php
namespace kOOL\Payment;

trait UidCypherTrait
{
	static protected function getCipher() {
		global $KOOL_ENCRYPTION_KEY;
		$cipher = new \phpseclib\Crypt\AES(\phpseclib\Crypt\AES::MODE_ECB);
		$cipher->setPassword($KOOL_ENCRYPTION_KEY);
		return $cipher;
	}
}
