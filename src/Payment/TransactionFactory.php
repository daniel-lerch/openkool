<?php
namespace kOOL\Payment;

class TransactionFactory
{
	use UidCypherTrait;

	static private $providers = null;

	static private $testMode = null;

	static private $objectCache = [];

	public static function setTestMode(bool $testMode): void
	{
		self::$testMode = $testMode;
		foreach(self::$providers as $provider) {
			$provider->setTestMode($testMode);
		}
	}

	private static function registerStandardProviders() {
		self::registerProvider(new PostFinanceCheckout\Provider);
	}

	public static function registerProvider($provider) {
		if(self::$testMode !== null) {
			$provider->setTestMode(self::$testMode);
		}
		self::$providers[$provider->getName()] = $provider;
	}

	public static function findByUid(string $uid): AbstractTransaction
	{
		$id = self::getCipher()->decrypt(hex2bin($uid));
		if(!$id || !ctype_digit($id)) {
			throw new \InvalidArgumentException('invalid uid');
		}
		return self::findById((int)$id);
	}

	public static function findById(int $id): AbstractTransaction
	{
		if(!isset(self::$objectCache[$id])) {
			$row = db_select_data('ko_payment_transaction','WHERE id='.$id,'*','','',true);
			if(!$row) {
				throw new TransactionNotFoundException('no transaction with id='.$id.' found');
			}
			self::$objectCache[$id] = self::getActiveProvider($row['provider'])->makeTransaction($row);
		}
		return self::$objectCache[$id];
	}

	public static function findByProviderId(string $providerId): AbstractTransaction
	{
		$providerId = db_get_link()->real_escape_string($providerId);
		$row = db_select_data('ko_payment_transaction','WHERE provider_id=\''.$providerId.'\'','*','','',true);
		if(!$row) {
			throw new TransactionNotFoundException('no transaction with provider_id='.$providerId.' found');
		}
		return self::getActiveProvider($row['provider'])->makeTransaction($row);
	}

	public static function getProvider(string $name): AbstractProvider
	{
		if(self::$providers === null) {
			self::registerStandardProviders();
		}
		if(!isset(self::$providers[$name])) {
			throw new \RuntimeException('no payment provider with name '.$name.' was registered');
		}
		return self::$providers[$name];
	}

	public static function getActiveProvider(string $name): AbstractProvider
	{
		$provider = self::getProvider($name);
		if(!$provider->isActive()) {
			throw new \RuntimeException('payment provider '.$name.' is not configured');
		}
		return $provider;
	}

	public static function getActiveProviders(): \Generator
	{
		if(self::$providers === null) {
			self::registerStandardProviders();
		}
		foreach(self::$providers as $name => $provider) {
			if($provider->isActive()) {
				yield $name => $provider;
			}
		}
	}
}
