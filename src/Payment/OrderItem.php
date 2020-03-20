<?php
namespace kOOL\Payment;

class OrderItem
{
	const TYPE_PRODUCT = 1;
	const TYPE_DISCOUNT = 2;
	const TYPE_FEE = 3;
	const TYPE_SHIPPING = 4;

	protected $amount;
	protected $name;
	protected $quantity;
	protected $type;
	protected $uniqueId;

	public function __construct(string $uniqueId,string $name, float $amount, int $type = self::TYPE_PRODUCT, int $quantity = 1)
	{
		$this->uniqueId = $uniqueId;
		$this->name = $name;
		$this->amount = round($amount,2);
		$this->quantity = $quantity;
		$this->type = $type;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getAmount(): float
	{
		return $this->amount;
	}

	public function setAmount(float $amount): void
	{
		$this->amount = $amount;
	}

	public function getQuantity(): int
	{
		return $this->quantity;
	}

	public function setQuantity(int $quantity): void
	{
		$this->quantity = $quantity;
	}

	public function getType(): int
	{
		return $this->type;
	}

	public function setType(int $type): void
	{
		$this->type = $type;
	}

	public function getUniqueId(): string
	{
		return $this->uniqueId;
	}

	public function setUniqueId(string $uniqueId): void
	{
		$this->uniqueId = $uniqueId;
	}

	public function needsShipping(): bool
	{
		return false;
	}

	public function getVATRate(): ?float
	{
		return null;
	}
}
