<?php
namespace kOOL\Payment\PostFinanceCheckout;

class PaymentMethod implements \kOOL\Payment\PaymentMethodInterface
{
	protected $config;

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	public function getName(): string
	{
		foreach($this->config['resolvedTitle'] as $lang => $title) {
			if(substr($lang,0,2) == $_SESSION['lang']) {
				return $title;
			}
		}
		return $this->config['title']['displayName'];
	}

	public function getDescription(): string
	{
		foreach($this->config['resolvedDescription'] as $lang => $description) {
			if(substr($lang,0,2) == $_SESSION['lang']) {
				return $description;
			}
		}
		return '';
		print_r($this->config);
	}

	public function getImage(): string
	{
		return $this->config['resolvedImageUrl'];
	}

	public function getId()
	{
		return $this->config['id'];
	}

	public function getProviderName(): string
	{
		return "PostFinanceCheckout";
	}
}
