<?php
namespace kOOL\Payment;

interface PaymentMethodInterface
{
	public function getName(): string;
	public function getDescription(): string;
	public function getImage(): string;
	public function getId();
	public function getProviderName(): string;
}
