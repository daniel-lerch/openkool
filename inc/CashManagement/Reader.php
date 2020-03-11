<?php
namespace LPC\LpcEsr\CashManagement;

//require_once __DIR__.'/../../vendor/autoload.php';

class Reader
{
	protected $remotePath = 'yellow-net-reports/';

	private $sftp;

	private $username;

	private $privateKey;

	protected $messageFolder;

	protected $processors = [];

	protected $messagesToProcess = [];

	protected $logger;

	public function __construct($host,$port) {
		$this->sftp = new \phpseclib\Net\SFTP($host,$port);
		$this->privateKey = new \phpseclib\Crypt\RSA();
	}

	public function getMessagesToProcess() {
		return $this->messagesToProcess;
	}

	public function setUsername($username) {
		$this->username = $username;
	}

	public function setPrivateKey($privateKey) {
		$this->privateKey->loadKey($privateKey);
	}

	public function registerProcessor(ProcessorInterface $processor) {
		$this->processors[] = $processor;
	}

	public function setMessageFolder($messageFolder) {
		$this->messageFolder = rtrim($messageFolder,'/').'/';
	}

	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	public function readAll() {
		if(!$this->sftp->login($this->username,$this->privateKey)) {
			throw new \Exception('Login to SFTP server failed');
		}
		$files = $this->sftp->nlist($this->remotePath);
		@mkdir($this->messageFolder,0777,true);
		@mkdir($this->messageFolder.'done');
		if(!file_exists($this->messageFolder.'.htaccess')) {
			file_put_contents($this->messageFolder.'.htaccess','Deny from all');
		}
		foreach($files as $file) {
			if(in_array(substr($file,-4),['.zip','.xml']) &&
				!file_exists($this->messageFolder.$file) &&
				!file_exists($this->messageFolder.'done/'.$file)) {
				$this->sftp->get($this->remotePath.$file,$this->messageFolder.$file);
			}
		}
		$dir = dir($this->messageFolder);
		while(($file = $dir->read()) !== false) {
			$path = $this->messageFolder.$file;
			if($file[0] != '.' && is_file($path)) {
				$this->processFile($path);
			}
		}
		foreach($this->processors as $processor) {
			$processor->finalize();
		}
	}

	protected function processFile($path) {
		$this->messagesToProcess = [];
		$done = false;
		try {
			if(substr($path,-4) == '.zip') {
				$this->parseZipFile($path);
			} else if(substr($path,-4) == '.xml') {
				$this->parseXml(file_get_contents($path),basename($path));
			}
			$done = true;
		} catch(ParsingException $ex) {
			if($ex->isPermanent()) {
				$done = true;
			}
			if($this->logger) {
				$this->logger->reportError('Parsing Error in '.basename($path).': '.$ex->getMessage());
			}
		}
		if($done) {
			foreach($this->messagesToProcess as $message) {
				$this->handleMessage($message);
				if($this->logger) {
					$this->logger->logMessage($message);
				}
			}
			rename($path,$this->messageFolder.'done/'.basename($path));
		}
	}

	protected function parseZipFile($file) {
		$zip = new \ZipArchive;
		if($zip->open($file) !== true) {
			throw new ParsingException('Could not open zip file');
		}
		for($i = 0; $i < $zip->numFiles; $i++) {
			if(substr($zip->getNameIndex($i),-4) == '.xml') {
				$this->parseXml($zip->getFromIndex($i),$zip->getNameIndex($i),basename($file));
			}
		}
	}

	/**
	 * Read XML file for camt.053 / camt.054 entries and create \CashManagement\message objects
	 * @param      $xml
	 * @param      $xmlFile
	 * @param null $zipFile
	 * @throws ParsingException
	 */
	public function parseXml($xml, $xmlFile, $zipFile = null) {
		try {
			$doc = new \SimpleXMLElement($xml);
		} catch(\Exception $ex) {
			throw new ParsingException($ex->getMessage(),true,0,$ex);
		}
		$namespaces = $doc->getNamespaces();
		if($namespaces[''] == 'urn:iso:std:iso:20022:tech:xsd:camt.054.001.04') {
			if(!isset($doc->BkToCstmrDbtCdtNtfctn)) {
				throw new ParsingException("Camt.054 does not contain expected structure BkToCstmrDbtCdtNtfctn",true,0,$ex);
			}

			foreach($doc->BkToCstmrDbtCdtNtfctn->Ntfctn as $notification) {
				$crdate = new \DateTime((string)$notification->CreDtTm);
				$notificationId = (string)$notification->Id;
				$accountNumber = $accountName = null;
				if(isset($notification->Acct)) {
					$accountNumber = $this->parseAccountNumber($notification->Acct->Id);
					if(isset($notification->Acct->Ownr) && isset($notification->Acct->Ownr->Nm)) {
						$accountName = $notification->Acct->Ownr->Nm;
					}
				}
				foreach($notification->Ntry as $entry) {
					if($this->shouldProcessEntry($entry)) {
						$message = new Message;
						$message->setZipFile($zipFile);
						$message->setCamtFile($xmlFile);
						$message->setNotificationId($notificationId);
						$message->setCrdate($crdate);
						$message->setAccountNumber($accountNumber);
						$message->setAccountName($accountName);
						$this->parseNotificationEntry($entry,$message);
					}
				}
			}
		} elseif ($namespaces[''] == 'urn:iso:std:iso:20022:tech:xsd:camt.053.001.04') {
			if(!isset($doc->BkToCstmrStmt)) {
				throw new ParsingException("Camt.053 does not contain expected structure BkToCstmrStmt",true,0,$ex);
			}

			$crdate = new \DateTime((string)$doc->BkToCstmrStmt->Stmt->CreDtTm);
			$notificationId = (string)$doc->BkToCstmrStmt->Stmt->Id;

			$accountNumber = $accountName = null;
			if(isset($doc->BkToCstmrStmt->Stmt->Acct)) {
				$accountNumber = $this->parseAccountNumber($doc->BkToCstmrStmt->Stmt->Acct->Id);
				if(isset($doc->BkToCstmrStmt->Stmt->Acct->Ownr) && isset($doc->BkToCstmrStmt->Stmt->Acct->Ownr->Nm)) {
					$accountName = $doc->BkToCstmrStmt->Stmt->Acct->Ownr->Nm;
				}
			}

			foreach($doc->BkToCstmrStmt->Stmt->Ntry AS $entry) {
				if($this->shouldProcessEntry($entry)) {
					$message = new Message;
					$message->setZipFile($zipFile);
					$message->setCamtFile($xmlFile);
					$message->setNotificationId($notificationId);
					$message->setCrdate($crdate);
					$message->setAccountNumber($accountNumber);
					$message->setAccountName($accountName);
					$this->parseNotificationEntry($entry,$message);
				}
			}
		}
	}


	/**
	 * @param \SimpleXMLElement $entry (ReportEntry4)
	 */
	protected function shouldProcessEntry(\SimpleXMLElement $entry) {
		if((string)$entry->CdtDbtInd != 'CRDT') return false; // this is not an incoming payment (but DBIT = outgoing)
		if((string)$entry->Sts != 'BOOK') return false; // this payment is not done yet but pending (PDNG)
		if(isset($entry->RvslInd) && (string)$entry->RvslInd && (string)$entry->RvslInd != 'false') return false; // this is a reversal payment (after a previous DBIT payment)
		return true;
	}

	/**
	 * @param \SimpleXMLElement $entry (ReportEntry4)
	 * @param \LPC\LpcEsr\CashManagement\Message $message
	 */
	protected function parseNotificationEntry(\SimpleXMLElement $entry,Message $message) {
		$message->setAmount((string)$entry->Amt);
		$message->setCurrency((string)$entry->Amt['Ccy']);
		if(isset($entry->NtryRef)) {
			$participantNumber = (string)$entry->NtryRef;
			if(ctype_digit($participantNumber) && strlen($participantNumber) == 9) {
				$participantNumber = substr($participantNumber,0,2).'-'.substr($participantNumber,2,6).'-'.substr($participantNumber,8);
			}
			$message->setParticipantNumber($participantNumber);
		}
		if(isset($entry->BookgDt)) {
			$message->setBookingDate($this->parseDateAndDateTimeChoice($entry->BookgDt));
		}
		if(isset($entry->ValDt)) {
			$message->setValutaDate($this->parseDateAndDateTimeChoice($entry->ValDt));
		}
		if(isset($entry->AddtlNtryInf)) {
			$message->setAdditionalInformation((string)$entry->AddtlNtryInf);
		}
		if(isset($entry->BkTxCd)) {
			$message->setBankTransactionCode($this->parseBankTransactionCode($entry->BkTxCd));
		}
		if(isset($entry->NtryDtls)) {
			foreach($entry->NtryDtls as $entryDetails) {
				foreach($entryDetails->TxDtls as $transaction) {
					if($transaction->CdtDbtInd == 'CRDT') { // is incoming payment
						$clonedMessage = clone $message;
						$this->parseTransaction($transaction,$clonedMessage);
						$this->messagesToProcess[] = $clonedMessage;
					}
				}
			}
		} else {
			if(isset($entry->Chrgs)) {
				$message->setCharges($this->parseCharges($entry->Chrgs));
			}
			$this->messagesToProcess[] = $message;
		}
	}

	/**
	 * @param \SimpleXMLElement $entry (EntryTransaction4)
	 * @param \LPC\LpcEsr\CashManagement\Message $message
	 */
	protected function parseTransaction(\SimpleXMLElement $transaction,Message $message) {
		$message->setAmount((string)$transaction->Amt);
		$message->setCurrency((string)$transaction->Amt['Ccy']);
		if(isset($transaction->Chrgs)) {
			$message->setCharges($this->parseCharges($transaction->Chrgs));
		}
		if(isset($transaction->Refs)) {
			if(isset($transaction->Refs->AcctSvcrRef)) {
				$message->setAccountServicerReference((string)$transaction->Refs->AcctSvcrRef);
			}
		}
		if(isset($transaction->RltdPties)) {
			if(isset($transaction->RltdPties->Dbtr)) {
				$this->parseDebtor($transaction->RltdPties->Dbtr,$message);
			} else if(isset($transaction->RltdPties->InitgPty)) {
				$this->parseDebtor($transaction->RltdPties->InitgPty,$message);
			} else if(isset($transaction->RltdPties->UltmtDbtr)) {
				$this->parseDebtor($transaction->RltdPties->UltmtDbtr,$message);
			}
			if(isset($transaction->RltdPties->DbtrAcct)) {
				$message->setDebtorAccountNumber($this->parseAccountNumber($transaction->RltdPties->DbtrAcct->Id));
			}
		}
		if(isset($transaction->Purp)) {
			if(isset($transaction->Purp->Prtry)) {
				$message->setPurpose((string)$transaction->Purp->Prtry);
			} else {
				$message->setPurposeCode((string)$transaction->Purp->Cd);
			}
		}
		if(isset($transaction->RmtInf)) {
			if(isset($transaction->RmtInf->Ustrd)) {
				$notes = [];
				foreach($transaction->RmtInf->Ustrd as $note) {
					$notes[] = $note;
				}
				$message->setNote(implode("\n",$notes));
			}
			if(isset($transaction->RmtInf->Strd)) {
				$referenceNumbers = [];
				foreach($transaction->RmtInf->Strd as $structuredRemittanceInformation) {
					if(isset($structuredRemittanceInformation->CdtrRefInf->Ref)) {
						$referenceNumbers[] = (string)$structuredRemittanceInformation->CdtrRefInf->Ref;
					}
					if(isset($structuredRemittanceInformation->AddtlRmtInf)) {
						if(preg_match('/^\?REJECT\?(\d)$/',$structuredRemittanceInformation->AddtlRmtInf,$m)) {
							$message->setReject($m[1] != '0');
						}
					}
				}
				$message->setReferenceNumber(implode("\n",$referenceNumbers));
			}
		}
	}

	protected function parseDateAndDateTimeChoice(\SimpleXMLElement $datetime) {
		if(isset($datetime->Dt)) {
			return new \DateTime((string)$datetime->Dt);
		} else {
			return new \DateTime((string)$datetime->DtTm);
		}
	}

	protected function parseCharges(\SimpleXMLElement $charges) {
		if(isset($charges->TtlChrgsAndTaxAmt)) {
			return (string)$charges->TtlChrgsAndTaxAmt;
		}
		$total = 0;
		foreach($charges->Rcrd as $record) {
			$total += (float)$record->Amt;
		}
		return $total;
	}

	protected function parseDebtor(\SimpleXMLElement $debtor,Message $message) {
		if(isset($debtor->Id)) {
			$identifications = [];
			$otherIdentifications = null;
			if(isset($debtor->Id->OrgId)) {
				if(isset($debtor->Id->OrgId->AnyBIC)) {
					$identifications['BIC'] = (string)$debtor->Id->OrgId->AnyBIC;
				}
				if(isset($debtor->Id->OrgId->Othr)) {
					$otherIdentifications = $debtor->Id->OrgId->Othr;
				}
			} else {
				if(isset($debtor->Id->PrvtId->DateAndPlaceOfBirth)) {
					$identifications['DateOfBirth'] = (string)$debtor->Id->PrvId->DtAndPlcOfBirth->BirthDt;
					$identifications['CityOfBirth'] = (string)$debtor->Id->PrvId->DtAndPlcOfBirth->CityOfBirth;
					$identifications['CountryOfBirth'] = (string)$debtor->Id->PrvId->DtAndPlcOfBirth->CtryOfBirth;
				}
				if(isset($debtor->Id->PrvtId->Othr)) {
					$otherIdentifications = $debtor->Id->PrvtId->Othr;
				}
			}
			if($otherIdentifications) {
				foreach($otherIdentifications as $otherIdentification) {
					$identification = (string)$otherIdentification->Id;
					if(isset($otherIdentification->SchmeNm)) {
						$identifications[(string)$otherIdentification->SchmeNm->Prtry] = $identification;
					} else {
						$identifications[] = $identification;
					}
				}
			}
			$message->setDebtorIdentification(json_encode($identifications));
		}
		if(isset($debtor->Nm)) {
			$message->setDebtorName((string)$debtor->Nm);
		} else if(isset($debtor->CtctDtls->Nm)) {
			$message->setDebtorName((string)$debtor->CtctDtls->Nm);
		}
		$anyAdressInfo = false;
		if(isset($debtor->PstlAdr->StrtNm)) {
			$street = (string)$debtor->PstlAdr->StrtNm;
			if(isset($debtor->PstlAdr->BldgNb)) {
				$street .= ' '.$debtor->PstlAdr->BldgNb;
			}
			$message->setDebtorStreet($street);
			$anyAdressInfo = true;
		}
		if(isset($debtor->PstlAdr->PstCd)) {
			$message->setDebtorZip((string)$debtor->PstlAdr->PstCd);
			$anyAdressInfo = true;
		}
		if(isset($debtor->PstlAdr->TwnNm)) {
			$message->setDebtorCity((string)$debtor->PstlAdr->TwnNm);
			$anyAdressInfo = true;
		}
		if(isset($debtor->PstlAdr->Ctry)) {
			$message->setDebtorCountry((string)$debtor->PstlAdr->Ctry);
			$anyAdressInfo = true;
		} else if(isset($debtor->CtryOfRes)) {
			$message->setDebtorCountry((string)$debtor->CtryOfRes);
			$anyAdressInfo = true;
		}
		$extraLines = [];
		if(isset($debtor->PstlAdr->AdrLine)) {
			foreach($debtor->PstlAdr->AdrLine as $extraLine) {
				$extraLines[] = (string)$extraLine;
			}
			if($anyAdressInfo || count($extraLines) >= 1) {
				// try to find more information about address
				$foundAddress = ko_fuzzy_address_search($extraLines);
				if(empty($message->getDebtorName()) && !empty($foundAddress['firm'])) {
					$message->setDebtorName($foundAddress['firm']);
				}
				if(empty($message->getDebtorZip()) && !empty($foundAddress['zip'])) {
					$message->setDebtorZip($foundAddress['zip']);
				}
				if(empty($message->getDebtorCity()) && !empty($foundAddress['city'])) {
					$message->setDebtorCity($foundAddress['city']);
				}
				if(empty($message->getDebtorStreet()) && !empty($foundAddress['address'])) {
					$message->setDebtorStreet($foundAddress['address']);
				} else {
					$message->setDebtorExtraAddressLines(implode("\n",$extraLines));
				}
			} else {
				$message->setDebtorStreet(array_shift($extraLines));
				$last = array_pop($extraLines);
				if(preg_match('/^((?:[A-Z]{2}-)?\d{4}\d*)\s+(.+)$/',$last,$m)) {
					$message->setDebtorZip($m[1]);
					$message->setDebtorCity($m[2]);
					if($extraLines) {
						$message->setDebtorExtraAddressLines(implode("\n",$extraLines));
					}
				} else if($extraLines) {
					$secondLast = array_pop($extraLines);
					if(preg_match('^/((?:[A-Z]{2}-)?\d{4}\d*)\s+(.+)$/',$last,$m)) {
						$message->setDebtorZip($m[1]);
						$message->setDebtorCity($m[2]);
						$message->setDebtorCountry($last);
						if($extraLines) {
							$message->setDebtorExtraAddressLines(implode("\n",$extraLines));
						}
					}
				} else {
					$message->setDebtorExtraAddressLines(implode("\n",$extraLines));
				}
			}
		}

//		$debugAddress = [
//			"name" => $message->getDebtorName(),
//			"address" => $message->getDebtorStreet(),
//			"zip" => $message->getDebtorZip(),
//			"city" => $message->getDebtorCity(),
//		];

		if(isset($debtor->CtctDtls->NmPrfx)) {
			$prefix = (string)$debtor->CtctDtls->NmPrfx;
			$gender = '';
			if($prefix == 'MIST') {
				$gender = 'm';
			} else if($prefix == 'MISS' || $prefix == 'MADM') {
				$gender = 'f';
			}
			$message->setDebtorGender($gender);
		}
		if(isset($debtor->CtctDtls->PhneNb)) {
			$message->setDebtorPhone((string)$debtor->CtctDtls->PhneNb);
		}
		if(isset($debtor->CtctDtls->MobNb)) {
			$message->setDebtorMobile((string)$debtor->CtctDtls->MobNb);
		}
		if(isset($debtor->CtctDtls->EmailAdr)) {
			$message->setDebtorEmail((string)$debtor->CtctDtls->EmailAdr);
		}
	}

	/**
	 * @param \SimpleXMLElement $entry (AccountIdentification4Choice)
	 */
	protected function parseAccountNumber(\SimpleXMLElement $account) {
		if(isset($account->IBAN)) {
			return (string)$account->IBAN;
		}
		if(isset($account->Othr)) {
			return (string)$account->Othr->Id;
		}
	}

	/**
	 * @param \SimpeXMLElement $bktxcd (BankTransactionCodeStructure4)
	 */
	protected function parseBankTransactionCode(\SimpleXMLElement $bktxcd) {
		if(isset($bktxcd->Domn)) {
			return implode('.',[
				$bktxcd->Domn->Cd,
				$bktxcd->Domn->Fmly->Cd,
				$bktxcd->Domn->Fmly->SubFmlyCd,
			]);
		}
		if(isset($bktxcd->Prtry)) {
			return $bktxcd->Prtry->Cd;
		}
	}

	protected function handleMessage(Message $message) {
		$errorMessage = 'No processor could process this message';
		try {
			foreach($this->processors as $processor) {
				if($processor->canProcess($message)) {
					if($processor->process($message)) {
						$message->setHandeled();
						return true;
					}
				}
			}
		} catch(ProcessingException $ex) {
			$errorMessage = $ex->getMessage();
		}
		$message->addError($errorMessage);
		if($this->logger) {
			$this->logger->reportError($errorMessage,$message);
		}
		return false;
	}
}
