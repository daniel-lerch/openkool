<?php
namespace LPC\LpcEsr\CashManagement;

class Message
{
	const STATE_UNHANDELED = 0;
	const STATE_HANDELED = 1;
	const STATE_HANDELED_MANUALLY = 2;
	const STATE_FAILED = 3;

	/**
	 * @var string
	 */
	protected $notificationId;

	/**
	 * 0: unhandeled
	 * 1: handeled
	 * 2: handeled manually
	 * 3: failed
	 *
	 * @var int
	 */
	protected $state = self::STATE_UNHANDELED;

	/**
	 * the added errors
	 *
	 * @var string
	 */
	protected $errors;

	/**
	 * amount in the currency of the account
	 * format 0.00
	 *
	 * @var string
	 */
	protected $amount;

	/**
	 * the currency (is always the account's currency)
	 *
	 * @var string
	 */
	protected $currency;

	/**
	 * @var string
	 */
	protected $charges;

	/**
	 * the ESR reference number
	 * technically this property can hold several numbers. If so, they get separated by newlines.
	 * If the type of a number is specified too, it is prepended to the number, separated by ':'.
	 *
	 * @var string
	 */
	protected $referenceNumber;

	/**
	 * Unique reference for the entry, assigned by the financial institution
	 *
	 * @var string
	 */
	protected $accountServicerReference;

	/**
	 * @var string
	 */
	protected $note;

	/**
	 * the creation datetime
	 *
	 * @var \DateTime
	 */
	protected $crdate;

	/**
	 * the booking date (time part may not be set)
	 *
	 * @var \DateTime
	 */
	protected $bookingDate;

	/**
	 * the valuta date (time part may not be set)
	 *
	 * @var \DateTime
	 */
	protected $valutaDate;

	/**
	 * the creditor account, usually as IBAN
	 *
	 * @var string
	 */
	protected $accountNumber;

	/**
	 * name of creditor account
	 *
	 * @var string
	 */
	protected $accountName;

	/**
	 * the esr participant number (PC-Konto)
	 * can also hold a ESR-IBAN or a BESR-id
	 *
	 * @var string
	 */
	protected $participantNumber;

	/**
	 * @var string
	 */
	protected $debtorIdentification;

	/**
	 * @var string
	 */
	protected $debtorName;

	/**
	 * @var string
	 */
	protected $debtorStreet;

	/**
	 * @var string
	 */
	protected $debtorZip;

	/**
	 * @var string
	 */
	protected $debtorCity;

	/**
	 * country code (alpha 2)
	 *
	 * @var string
	 */
	protected $debtorCountry;

	/**
	 * @var string
	 */
	protected $debtorPhone;

	/**
	 * @var string
	 */
	protected $debtorMobile;

	/**
	 * @var string
	 */
	protected $debtorEmail;

	/**
	 * can be '', 'm', 'f'
	 *
	 * @var string
	 */
	protected $debtorGender;

	/**
	 * @var string
	 */
	protected $debtorAccountNumber;

	/**
	 * additional address lines (e.g. Postfach) separated by newlines (max 7)
	 *
	 * @var string
	 */
	protected $debtorExtraAddressLines;

	/**
	 * the financial institut may send additional informations (max 500 chars)
	 *
	 * @var string
	 */
	protected $additionalInformation;

	/**
	 * the specified payment purpose
	 *
	 * @var string
	 */
	protected $purpose;

	/**
	 * a payment purpose code
	 * @see https://www.iso20022.org/external_code_list.page
	 *
	 * @var string
	 */
	protected $purposeCode;

	/**
	 * is reject (or mass reject)
	 *
	 * @var boolean
	 */
	protected $reject;

	/**
	 * @var string
	 */
	protected $bankTransactionCode;

	/**
	 * @var string
	 */
	protected $zipFile;

	/**
	 * @var string
	 */
	protected $camtFile;

	static protected $bankTransactionCodes = null;

	/**
	 * @return string
	 */
	public function getDebtorExtraAddressLines() {
		return $this->debtorExtraAddressLines;
	}

	/**
	 * @param string $debtorExtraAddressLines
	 */
	public function setDebtorExtraAddressLines($debtorExtraAddressLines) {
		$this->debtorExtraAddressLines = $debtorExtraAddressLines;
	}

	/**
	 * @return string
	 */
	public function getNotificationId() {
		return $this->notificationId;
	}

	/**
	 * @param string $notificationId
	 */
	public function setNotificationId($notificationId) {
		$this->notificationId = $notificationId;
	}

	/**
	 * @return int
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * mark message as handeled
	 */
	public function setHandeled($manually = false) {
		$this->state = $manually ? STATE_HANDELED_MANUALLY : STATE_HANDELED;
	}

	/**
	 * mark message as faulty
	 *
	 * @param string $error
	 */
	public function addError($error) {
		$this->state = STATE_FAILED;
		if(strpos($error,"\n")) {
			$error = '"'.str_replace('"','""',$error).'"';
		}
		if($this->errors) {
			$this->errors .= "\n";
		}
		$this->errors .= $error;
	}

	/**
	 * get the added errors as array
	 *
	 * @return array
	 */
	public function getErrors() {
		if($this->errors) {
			return str_getcsv($this->errors,"\n",'"');
		} else {
			return [];
		}
	}

	public function getAmount() {
		return $this->amount;
	}

	public function setAmount($amount) {
		$this->amount = $amount;
	}

	public function getCurrency() {
		return $this->currency;
	}

	public function setCurrency($currency) {
		$this->currency = $currency;
	}

	/**
	 * @return string
	 */
	public function getCharges() {
		return $this->charges;
	}

	/**
	 * @param string $charges
	 */
	public function setCharges($charges) {
		$this->charges = $charges;
	}

	public function getReferenceNumber() {
		return $this->referenceNumber;
	}

	public function setReferenceNumber($referenceNumber) {
		$this->referenceNumber = $referenceNumber;
	}

	/**
	 * @return string
	 */
	public function getAccountServicerReference() {
		return $this->accountServicerReference;
	}

	/**
	 * @param string
	 */
	public function setAccountServicerReference($accountServicerReference) {
		$this->accountServicerReference = $accountServicerReference;
	}

	/**
	 * @return string
	 */
	public function getUniqueId() {
		if($this->accountServicerReference) {
			return $this->accountServicerReference;
		} else {
			return md5(serialize([
				$this->notificationId,
				$this->amount,
				$this->currency,
				$this->referenceNumber,
				$this->crdate,
				$this->bookingDate,
				$this->valutaDate,
				$this->accountNumber,
				$this->participantNumber,
			]));
		}
	}

	/**
	 * @return string
	 */
	public function getNote() {
		return $this->note;
	}

	/**
	 * @param string $note
	 */
	public function setNote($note) {
		$this->note = $note;
	}

	public function getCrdate() {
		return $this->crdate;
	}

	public function setCrdate(\DateTime $crdate) {
		$this->crdate = $crdate;
	}

	/**
	 * @return \DateTime
	 */
	public function getBookingDate() {
		return $this->bookingDate;
	}

	/**
	 * @param \DateTime $bookingDate
	 */
	public function setBookingDate(\DateTime $bookingDate) {
		$this->bookingDate = $bookingDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getValutaDate() {
		return $this->valutaDate;
	}

	/**
	 * @param \DateTime $valutaDate
	 */
	public function setValutaDate($valutaDate) {
		$this->valutaDate = $valutaDate;
	}

	/**
	 * @return string
	 */
	public function getAccountNumber() {
		return $this->accountNumber;
	}

	/**
	 * @param string $accountNumber
	 */
	public function setAccountNumber($accountNumber) {
		$this->accountNumber = $accountNumber;
	}

	/**
	 * @return string
	 */
	public function getAccountName() {
		return $this->accountName;
	}

	/**
	 * @param string $accountName
	 */
	public function setAccountName($accountName) {
		$this->accountName = $accountName;
	}

	/**
	 * @return string
	 */
	public function getParticipantNumber() {
		return $this->participantNumber;
	}

	/**
	 * @param string $participantNumber
	 */
	public function setParticipantNumber($participantNumber) {
		$this->participantNumber = $participantNumber;
	}

	/**
	 * @return string
	 */
	public function getDebtorIdentification() {
		return $this->debtorIdentification;
	}

	/**
	 * @param string $debtorIdentification
	 */
	public function setDebtorIdentification($debtorIdentification) {
		$this->debtorIdentification = $debtorIdentification;
	}

	/**
	 * @return string
	 */
	public function getDebtorName() {
		return $this->debtorName;
	}

	/**
	 * @param string $debtorName
	 */
	public function setDebtorName($debtorName) {
		$this->debtorName = $debtorName;
	}

	/**
	 * @return string
	 */
	public function getDebtorStreet() {
		return $this->debtorStreet;
	}

	/**
	 * @param string $debtorStreet
	 */
	public function setDebtorStreet($debtorStreet) {
		$this->debtorStreet = $debtorStreet;
	}

	/**
	 * @return string
	 */
	public function getDebtorZip() {
		return $this->debtorZip;
	}

	/**
	 * @param string $debtorZip
	 */
	public function setDebtorZip($debtorZip) {
		$this->debtorZip = $debtorZip;
	}

	/**
	 * @return string
	 */
	public function getDebtorCity() {
		return $this->debtorCity;
	}

	/**
	 * @param string $debtorCity
	 */
	public function setDebtorCity($debtorCity) {
		$this->debtorCity = $debtorCity;
	}

	/**
	 * @return string
	 */
	public function getDebtorCountry() {
		return $this->debtorCountry;
	}

	/**
	 * @param string $debtorCountry
	 */
	public function setDebtorCountry($debtorCountry) {
		$this->debtorCountry = $debtorCountry;
	}

	/**
	 * @return string
	 */
	public function getDebtorPhone() {
		return $this->debtorPhone;
	}

	/**
	 * @param string $debtorPhone
	 */
	public function setDebtorPhone($debtorPhone) {
		$this->debtorPhone = $debtorPhone;
	}

	/**
	 * @return string
	 */
	public function getDebtorMobile() {
		return $this->debtorMobile;
	}

	/**
	 * @param string $debtorMobile
	 */
	public function setDebtorMobile($debtorMobile) {
		$this->debtorMobile = $debtorMobile;
	}

	/**
	 * @return string
	 */
	public function getDebtorEmail() {
		return $this->debtorEmail;
	}

	/**
	 * @param string $debtorEmail
	 */
	public function setDebtorEmail($debtorEmail) {
		$this->debtorEmail = $debtorEmail;
	}

	/**
	 * @return string
	 */
	public function getDebtorGender() {
		return $this->debtorGender;
	}

	/**
	 * @param string $debtorGender
	 */
	public function setDebtorGender($debtorGender) {
		$this->debtorGender = $debtorGender;
	}

	/**
	 * @return string
	 */
	public function getDebtorAccountNumber() {
		return $this->debtorAccountNumber;
	}

	/**
	 * @param string $debtorAccountNumber
	 */
	public function setDebtorAccountNumber($debtorAccountNumber) {
		$this->debtorAccountNumber = $debtorAccountNumber;
	}

	/**
	 * @return string
	 */
	public function getAdditionalInformation() {
		return $this->additionalInformation;
	}

	/**
	 * @param string $additionalInformation
	 */
	public function setAdditionalInformation($additionalInformation) {
		$this->additionalInformation = $additionalInformation;
	}

	/**
	 * @return string
	 */
	public function getPurposeCode() {
		return $this->purposeCode;
	}

	/**
	 * @param string $purposeCode
	 */
	public function setPurposeCode($purposeCode) {
		$this->purposeCode = $purposeCode;
	}

	/**
	 * @return boolean
	 */
	public function getReject() {
		return $this->reject;
	}

	/**
	 * @param boolean $reject
	 */
	public function setReject($reject) {
		$this->reject = $reject;
	}

	/**
	 * @return string
	 */
	public function getBankTransactionCode() {
		return $this->bankTransactionCode;
	}

	/**
	 * @param string $bankTransactionCode
	 */
	public function setBankTransactionCode($bankTransactionCode) {
		$this->bankTransactionCode = $bankTransactionCode;
	}

	/**
	 * @return string
	 */
	public function getTranslatedBankTransactionCode($lang) {
		return self::translateBankTransactionCode($this->bankTransactionCode,$lang);
	}

	/**
	 * @return string
	 */
	static public function translateBankTransactionCode($bankTransactionCode,$lang) {
		if(preg_match('/^([A-Z]{4})(?:\.([A-Z]{4})(?:\.([A-Z]{4}))?)?$/',$bankTransactionCode,$m)) {
			if(self::$bankTransactionCodes === null) {
				self::$bankTransactionCodes = require(__DIR__.'/BankTransactionCodes.php');
			}

			if(isset(self::$bankTransactionCodes[$m[1]]['fam'][$m[2]]['sub'][$m[3]][$lang])) {
				return self::$bankTransactionCodes[$m[1]]['fam'][$m[2]]['sub'][$m[3]][$lang];
			}
			if(isset(self::$bankTransactionCodes[$m[1]]['fam'][$m[2]]['sub'][$m[3]]['en'])) {
				return self::$bankTransactionCodes[$m[1]]['fam'][$m[2]]['sub'][$m[3]]['en'];
			}
			if(isset(self::$bankTransactionCodes[$m[1]]['fam'][$m[2]][$lang])) {
				return self::$bankTransactionCodes[$m[1]]['fam'][$m[2]][$lang];
			}
			if(isset(self::$bankTransactionCodes[$m[1]]['fam'][$m[2]]['en'])) {
				return self::$bankTransactionCodes[$m[1]]['fam'][$m[2]]['en'];
			}
			if(isset(self::$bankTransactionCodes[$m[1]][$lang])) {
				return self::$bankTransactionCodes[$m[1]][$lang];
			}
			if(isset(self::$bankTransactionCodes[$m[1]]['en'])) {
				return self::$bankTransactionCodes[$m[1]]['en'];
			}
		}
		return $bankTransactionCode;
	}

	/**
	 * @return string
	 */
	public function getPurpose() {
		return $this->purpose;
	}

	/**
	 * @param string $purpose
	 */
	public function setPurpose($purpose) {
		$this->purpose = $purpose;
	}

	/**
	 * @return string
	 */
	public function getZipFile() {
		return $this->zipFile;
	}

	/**
	 * @param string $zipFile
	 */
	public function setZipFile($zipFile) {
		$this->zipFile = $zipFile;
	}

	/**
	 * @return string
	 */
	public function getCamtFile() {
		return $this->camtFile;
	}

	/**
	 * @param string $camtFile
	 */
	public function setCamtFile($camtFile) {
		$this->camtFile = $camtFile;
	}

	public function _getProperties() {
		$properties = get_object_vars($this);
		foreach ($properties as $propertyName => $propertyValue) {
			if ($propertyName[0] === '_') {
				unset($properties[$propertyName]);
			}
		}
		return $properties;
	}
}
