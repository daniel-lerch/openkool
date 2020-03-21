<?php
namespace kOOL\Subscription;

class FormException extends \Exception
{
	protected $error;
	protected $statusCode;

	public function __construct($error,$statusCode = 200) {
		$this->error = $error;
		$this->statusCode = $statusCode;
		parent::__construct(getLL('subscription_form_error_'.$error));
	}

	public function getError() {
		return $this->error;
	}

}
