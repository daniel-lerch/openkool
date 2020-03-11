<?php
/**
 * Created by PhpStorm.
 * User: hess
 * Date: 20/07/17
 * Time: 14:37
 */

namespace LPC\LpcEsr\CashManagement;

class koProcessor implements \LPC\LpcEsr\CashManagement\ProcessorInterface {
	public $processedData = array();
	protected $doneRows = array();
	protected $doneTotal = array();
	protected $reportOnly = false;

	/**
	 * @return array
	 */
	public function getProcessedData()
	{
		return $this->processedData;
	}


	/**
	 * @return array
	 */
	public function getDoneTotal()
	{
		return $this->doneTotal;
	}

	/**
	 * @param array $doneTotal
	 */
	public function setDoneTotal($doneTotal)
	{
		$this->doneTotal = $doneTotal;
	}

	/**
	 * @return array
	 */
	public function getDoneRows()
	{
		return $this->doneRows;
	}

	/**
	 * @param array $doneRows
	 */
	public function setDoneRows($doneRows)
	{
		$this->doneRows = $doneRows;
	}

	/**
	 * @param boolean $reportOnly
	 */
	public function setReportOnly($reportOnly)
	{
		$this->reportOnly = $reportOnly;
	}

	/**
	 * return true if the processor can process this message
	 *
	 * @param \LPC\LpcEsr\CashManagement\Message $message
	 * @return boolean
	 */
	public function canProcess(\LPC\LpcEsr\CashManagement\Message $message)
	{
		global $PLUGINS,$WEB_LANGS;

		//Allow plugins to overwrite these calculations
		$handled = FALSE;
		$summary = $type = $status = NULL;
		foreach ($PLUGINS as $plugin) {
			$fun = $this->reportOnly ? 'my_vesr_camt_report_' : 'my_vesr_camt_payment_';
			if (function_exists($fun . $plugin['name'])) {
				list($handled, $type, $status, $summary) = call_user_func_array($fun . $plugin['name'], array($message));
				if ($handled) break;
			}
		}
		if (!$handled) {
			foreach (array('donations') as $m) {
				$fun = $this->reportOnly ? 'ko_vesr_camt_report_' : 'ko_vesr_camt_payment_';
				if (function_exists($fun . $m)) {
					list($handled, $type, $status, $summary) = call_user_func_array($fun . $m, array($message));
					if ($handled) break;
				}
			}
		}
		if (!$handled) {
			$type = 'none';
			$status = 'notFound';
		}
		$row = array(
			'type' => $type,
			'reason' => $status,
			'cruser' => $_SESSION['ses_userid'],
			'amount' => $message->getAmount(),
			'booking_date' => $message->getBookingDate()->format('Y-m-d'),
			'valuta_date' => $message->getValutaDate()->format('Y-m-d'),
			'refnumber' => $message->getReferenceNumber(),
			'charges' => $message->getCharges(),
			'crdate' => $message->getCrdate()->format('Y-m-d'),
			'currency' => $message->getCurrency(),
			'note' => utf8_decode($message->getNote()),
			'purpose' => $message->getPurpose(),
			'purpose_code' => $message->getPurposeCode(),
			'account_number' => $message->getAccountNumber(),
			'account_name' => utf8_decode($message->getAccountName()),
			'participant_number' => $message->getParticipantNumber(),
			'reject' => $message->getReject(),
			'source' => utf8_decode($message->getTranslatedBankTransactionCode(substr(reset($WEB_LANGS),0,2))),
			'file' => $message->getCamtFile(),
			'uid' => $message->getUniqueId(),
			'_p_city' => utf8_decode($message->getDebtorCity()),
			'_p_country' => utf8_decode($message->getDebtorCountry()),
			'_p_email' => utf8_decode($message->getDebtorEmail()),
			'_p_extra_address_lines' => $message->getDebtorExtraAddressLines(),
			'_p_gender' => ['m' => 'm','f' => 'w'][$message->getDebtorGender()],
			'_p_identification' => utf8_decode($message->getDebtorIdentification()),
			'_p_mobile' => $message->getDebtorMobile(),
			'_p_name' => utf8_decode($message->getDebtorName()),
			'_p_address' => utf8_decode($message->getDebtorStreet()),
			'_p_phone' => $message->getDebtorPhone(),
			'_p_zip' => $message->getDebtorZip(),
			'additional_information' => utf8_decode($message->getAdditionalInformation()),
			'note' => utf8_decode($message->getNote()),
		);
		foreach ($summary as $k => $v) {
			$row[$k] = $v;
		}
		if ($status != 'ok' && !$this->reportOnly) {
			$rowId = db_insert_data('ko_vesr_camt', $row);
			$row['id'] = $rowId;
			ko_log_diff('new_camt_row', $row);
		}

		$this->processedData[] = array('message' => $message, 'row' => $row, 'summary' => $summary, 'status' => $status, 'type' => $type);

		return TRUE;
	}

	/**
	 * process the payment
	 * return true if the message is processed
	 * return false if other processors may try to process the message
	 * throw \LPC\LpcEsr\CashManagement\ProcessingException if this is the proper processor but the message can not be handled
	 *
	 * @param \LPC\LpcEsr\CashManagement\Message $message
	 * @return boolean
	 */
	public function process(\LPC\LpcEsr\CashManagement\Message $message)
	{
		// everything was already done in canProcess
		return TRUE;
	}

	/**
	 * gets called after one run of the reader
	 * can for example be used to send reporting mails
	 */
	public function finalize()
	{
		$done = array();
		$totals = array();

		foreach($this->processedData as $processed) {
			$done[$processed['type']][$processed['status']][] = $processed['row'];
			$totals[$processed['type']]['fees'] += $processed['row']['charges'];
			$totals[$processed['type']]['amount'] += $processed['row']['amount'];
			$totals[$processed['type']]['num']++;
			$totals[$processed['type']]['rejects'] += $processed['row']['reject'];
		}
		ksort($totals);

		//move none to end
		if(isset($totals['none'])) {
			$none = $totals['none'];
			unset($totals['none']);
			$totals['none'] = $none;
		}

		foreach($totals as $typeTotals) {
			foreach($typeTotals as $v => $sum) {
				$totals['total'][$v] += $sum;
			}
		}

		$this->setDoneRows($done);
		$this->setDoneTotal($totals);
	}
}
