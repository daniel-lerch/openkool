<?php
namespace kOOL\Payment;

abstract class AbstractProvider
{
	protected function getConfiguration(): ?array
	{
		global $PAYMENT_PROVIDER_CONFIG;
		return $PAYMENT_PROVIDER_CONFIG[$this->getName()] ?? null;
	}

	public function isActive(): bool
	{
		return !empty($this->getConfiguration());
	}

	public function startTransaction(): AbstractTransaction
	{
		return $this->makeTransaction([]);
	}

	public function hasJSPaymentForm(): bool
	{
		return $this->makeTransaction([]) instanceof JSPaymentFormInterface;
	}

	abstract public function setTestMode(bool $testMode): void;

	abstract public function makeTransaction(array $row): AbstractTransaction;

	abstract public function getName(): string;
}
