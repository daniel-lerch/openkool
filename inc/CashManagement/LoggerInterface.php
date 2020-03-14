<?php
namespace LPC\LpcEsr\CashManagement;

interface LoggerInterface
{
	/**
	 * this method should store the message in a database table to log it
	 *
	 * @param \LPC\LpcEsr\CashManagement\Message $message
	 */
	public function logMessage(Message $message);

	/**
	 * gets called on a error (e.g. a ProcessingException thrown in a processor)
	 * logMessage() is called in any case, so this method should only care about reporting (emailing) the error
	 * the $message parameter is only set if a message could be created (would for example be NULL for xml parsing errors)
	 *
	 * @param string $errorMessage
	 * @param \LPC\LpcEsr\CashManagement\Message $message
	 */
	public function reportError($errorMessage,Message $message = null);
}
