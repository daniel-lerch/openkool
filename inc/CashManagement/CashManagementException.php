<?php
namespace LPC\LpcEsr\CashManagement;

class CashManagementException extends \Exception
{
	protected $permanent;

	/**
	 * @param string $message
	 * @param boolean $permanent If set, the file is moved to the 'failed' directory. If not, the file is left untouched and will be retried in the next go.
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($message = '',$permanent = true,$code = 0,\Exception $previous = null) {
		parent::__construct($message,$code,$previous);
		$this->permanent = $permanent;
	}

	public function isPermanent() {
		return $this->permanent;
	}
}


