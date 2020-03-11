<?php
namespace kOOL\Payment;

interface OrderInterface extends \JsonSerializable
{
	/**
	 * @returns array<\kOOL\Payment\OrderItem>
	 */
	public function getItems(): array;

	public function getPersonData(): array;

	/**
	 * @returns iterable<\kOOL\Payment\AbstractTransaction>
	 */
	public function getTransactions(): iterable;

	public function addTransaction(AbstractTransaction $transaction): void;

	public function transactionStatusChanged(int $status, AbstractTransaction $transaction): void;

	public static function fromJson(array $data): OrderInterface;
}
