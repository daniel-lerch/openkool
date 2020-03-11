<?php
namespace kOOL\Payment;

interface RedirectUrlInterface
{
	public function getSuccessUrl(AbstractTransaction $transaction): string;

	public function getFailedUrl(AbstractTransaction $transaction): string;
}
