<?php
namespace kOOL\Payment\PostFinanceCheckout;

class Provider extends \kOOL\Payment\AbstractProvider
{
	private $testMode = true;

	public function setTestMode(bool $testMode): void
	{
		$this->testMode = $testMode;
	}

	public function getSpaceId() {
		return (int)$this->getConfiguration()['space'];
	}

	public function makeTransaction(array $row): \kOOL\Payment\AbstractTransaction
	{
		return new Transaction($row,$this);
	}

	public function startTransaction(): \kOOL\Payment\AbstractTransaction
	{
		$transaction = parent::startTransaction();
		$transaction->create();
		return $transaction;
	}

	public function getName(): string
	{
		return 'PostFinanceCheckout';
	}

	public function get($url,$params = [],$expectJson = true) {
		return $this->request($url,$params,[],'GET',$expectJson);
	}

	public function post($url,$data,$params = [],$expectJson = true) {
		return $this->request($url,$params,$data,'POST',$expectJson);
	}

	private function request($url,$params,$data,$method,$expectJson = true) {
		$ch = curl_init();
		$headers = [
			'Accept-Language: '.$_SESSION['lang'],
		];
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		if($data) {
			curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
			$headers[] = 'Content-Type: application/json';
		}
		if($params) {
			$url .= strpos($url,'?') === false ? '?' : '&';
			$url .= http_build_query($params);
		}
		$url = '/api'.$url;
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
		curl_setopt($ch,CURLOPT_URL,'https://www.postfinance-checkout.ch'.$url);
		if($expectJson) {
			$headers[] = 'Accept: application/json';
		}
		foreach($this->getAuthHeaders($url,$method) as $name => $value) {
			$headers[] = $name.': '.$value;
		}
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		$response = curl_exec($ch);

		file_put_contents('/usr/home/kool2/public_html/givebyclick/pfcheckout.log',var_export([
			$method => [
				'url' => $url,
				'headers' => $headers,
				'body' => json_encode_latin1($data,false),
			],
			'response' => $response
		],true).PHP_EOL,FILE_APPEND);

		if($response === false) {
			throw new \Exception('curl error: '.curl_error($ch));
		}
		$decoded = $expectJson ? json_decode($response,true) : null;
		if(($status = curl_getinfo($ch,CURLINFO_RESPONSE_CODE)) != 200) {
			$message = isset($decoded['message']) ? $decoded['message'] : strip_tags($response);
			if($status == 409) {
				throw new ModelVersionConflictException($message);
			}
			throw new \Exception('http status '.$status.' on '.$method.' request to '.$url.': '.$message);
		}
		if($expectJson && $decoded === null) {
			throw new \Exception('response is not json: '.$response);
		}
		return $expectJson ? $decoded : $response;
	}

	private function getAuthHeaders($url,$method) {
		$headers = [
			'X-Mac-Version' => '1',
			'X-Mac-Userid' => (int)$this->getConfiguration()['userId'],
			'X-Mac-Timestamp' => time(),
		];
		$headers['X-Mac-Value'] = base64_encode(hash_hmac(
			'sha512',
			implode('|',$headers).'|'.strtoupper($method).'|'.$url,
			base64_decode($this->getConfiguration()['authKey']),
			true
		));
		return $headers;
	}

	protected function getConfiguration(): ?array
	{
		if($this->testMode === null) {
			throw new \BadMethodCallException('you need to call setTestMode before using this provider');
		}
		$configuration = parent::getConfiguration();
		return $configuration[$this->testMode ? 'test' : 'prod'];
	}

	public function mapStatus(string $state): int
	{
		switch($state) {
			case 'PENDING':
				return Transaction::STATE_PENDING;
			case 'CONFIRMED':
				return Transaction::STATE_CONFIRMED;
			case 'PROCESSING':
				return Transaction::STATE_PROCESSING;
			case 'AUTHORIZED':
				return Transaction::STATE_AUTHORIZED;
			case 'COMPLETED':
				return Transaction::STATE_COMPLETED;
			case 'FAILED':
				return Transaction::STATE_FAILED;
			case 'VOIDED':
				return Transaction::STATE_VOIDED;
			case 'FULFILL':
				return Transaction::STATE_FULFILL;
			case 'DECLINE':
				return Transaction::STATE_DECLINE;
		}
	}

	public function mapItemType(int $type): string
	{
		switch($type) {
			case \kOOL\Payment\OrderItem::TYPE_PRODUCT:
				return 'PRODUCT';
			case \kOOL\Payment\OrderItem::TYPE_SHIPPING:
				return 'SHIPPING';
			case \kOOL\Payment\OrderItem::TYPE_DISCOUNT:
				return 'DISCOUNT';
			case \kOOL\Payment\OrderItem::TYPE_FEE:
				return 'FEE';
		}
	}
}
