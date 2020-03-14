<?php

class openssl
{
	protected $algo;
	protected $secret;
	protected $ivSize;

	public function __construct($algo = 'AES-256-CBC') {
		$this->algo = $algo;
		$this->ivSize = openssl_cipher_iv_length($algo);
	}

	public function setKey($secret) {
		$this->secret = $secret;
	}

	public function encrypt($data) {
		$iv = openssl_random_pseudo_bytes($this->ivSize);
		$crypted = openssl_encrypt($data,$this->algo,$this->secret,OPENSSL_RAW_DATA,$iv);
		return chunk_split(base64_encode($iv.$crypted),64);
	}

	public function decrypt($data) {
		$data = base64_decode($data);
		$iv = substr($data,0,$this->ivSize);
		$crypted = substr($data,$this->ivSize);
		return openssl_decrypt($crypted,$this->algo,$this->secret,OPENSSL_RAW_DATA,$iv);
	}
}

