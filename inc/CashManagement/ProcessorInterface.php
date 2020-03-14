<?php
namespace LPC\LpcEsr\CashManagement;

interface ProcessorInterface
{
	/**
	 * return true if the processor can process this message
	 *
	 * @param \LPC\LpcEsr\CashManagement\Message $message
	 * @return boolean
	 */
	public function canProcess(Message $message);

	/**
	 * process the payment
	 * return true if the message is processed
	 * return false if other processors may try to process the message
	 * throw \LPC\LpcEsr\CashManagement\ProcessingException if this is the proper processor but the message can not be handled
	 *
	 * @param \LPC\LpcEsr\CashManagement\Message $message
	 * @return boolean
	 */
	public function process(Message $message);

	/**
	 * gets called after one run of the reader
	 * can for example be used to send reporting mails
	 */
	public function finalize();
}
