<?php
namespace kOOL\Payment;

abstract class AbstractTransaction
{
	use UidCypherTrait;

	/**
	 * STATE_ constants correspond to postfinance-checkout states
	 * @see https://www.postfinance-checkout.ch/de-ch/doc/payment/transaction-process#_transaction_states
	 */
	const STATE_DRAFT = 0; // provider does not know of this transaction yet
	const STATE_PENDING = 1; // transaction is created
	const STATE_CONFIRMED = 2; // transaction is ready for starting payment process
	const STATE_PROCESSING = 3; // payment is processing on providers side
	const STATE_AUTHORIZED = 4; // authorized but not charged yet
	const STATE_COMPLETED = 5; // initiated, no money transferred yet
	const STATE_FAILED = 6; // failed
	const STATE_VOIDED = 7; // authorized payment got voided
	const STATE_FULFILL = 8; // money is transferred
	const STATE_DECLINE = 9; // canceled

	/**
	 * @var AbstractProvider
	 */
	protected $provider;

	/**
	 * the id if this object is already persisted, null otherwise
	 * @var ?int
	 */
	protected $id;

	/**
	 * any identificator that the remote side has assigned to this transaction
	 * @var string
	 */
	protected $providerId;

	/**
	 * the status, one of the STATE_ constants
	 * @var int
	 */
	private $status;

	/**
	 * userStatus can be used to store the status that has been showed to the visitor.
	 * this can divert from the status variable if for example, the user has been shown
	 * a success message but the transaction is afterwards marked as failed e.g. by a
	 * webhook request.
	 *
	 * one of the STATE_ constants
	 *
	 * @var int
	 */
	protected $userStatus;

	/**
	 * the date when the transaction started
	 * @var \DateTimeImmutable
	 */
	protected $crdate;

	/**
	 * the valuta date
	 * @var \DateTimeImmutable
	 */
	protected $completionDate;

	/**
	 * the associated order
	 * @var \OrderInterface
	 */
	protected $order;

	/**
	 * a newline-separated list of logged errors
	 * @var string
	 */
	protected $errors;

	/**
	 * the language (two letter iso) in which the transaction was started
	 * @var string
	 */
	protected $userLanguage;

	public function __construct(array $row = [], AbstractProvider $provider)
	{
		$this->provider = $provider;
		assert(!isset($row['provider']) || $row['provider'] == $this->provider->getName());
		$this->id = isset($row['id']) ? (int)$row['id'] : null;
		$this->providerId = $row['provider_id'] ?? '';
		$this->status = (int)($row['status'] ?? STATE_DRAFT);
		$this->userStatus = (int)($row['user_status'] ?? STATE_DRAFE);
		$this->crdate = new \DateTimeImmutable($row['crdate'] ?? 'now');
		$this->completionDate = $row['completion_date'] ? new \DateTimeImmutable($row['completion_date']) : null;
		$this->errors = $row['errors'];
		$this->userLanguage = ($row['user_language'] ?? '') ?: $_SESSION["lang"];
		if(!empty($row['order_type']) && !empty($row['order'])) {
			$this->order = $row['order_type']::fromJson(json_decode($row['order'],true));
		}
	}

	/**
	 * called when the order should be confirmed. after this a payment
	 * process can be started. the associated data (order object) must
	 * not change, after a transaction is confirmed
	 */
	abstract public function confirm(): void;

	/**
	 * return the used payment method as string
	 */
	abstract public function getPaymentMethod(): ?string;

	public function getOrder(): OrderInterface {
		return $this->order;
	}

	public function setOrder(OrderInterface $order): void {
		$this->order = $order;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUid(): string
	{
		if($this->id === null) {
			$this->persist();
		}
		return bin2hex(self::getCipher()->encrypt($this->id));
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	protected function setStatus(int $status): void
	{
		if($this->order && $this->status != $status) {
			$this->order->transactionStatusChanged($status,$this);
		}
		$this->status = $status;
	}

	public function getUserStatus(): int
	{
		return $this->userStatus;
	}

	public function setUserStatus(int $userStatus): void
	{
		$this->userStatus = $userStatus;
	}

	public function getCrDate(): \DateTimeImmutable
	{
		return $this->crdate;
	}

	public function getCompletionDate(): ?\DateTimeImmutable
	{
		return $this->completionDate;
	}

	public function getProviderId(): string
	{
		return $this->providerId;
	}

	public function getProvider(): AbstractProvider
	{
		return $this->provider;
	}

	public function getLastErrorMessage(): ?string
	{
		return $this->getErrorMessages()->current();
	}

	public function getErrorMessages(): \Generator
	{
		foreach($this->getErrors() as $time => $message) {
			yield $message;
		}
	}

	public function getErrors(): \Generator
	{
		$p = 0;
		$done = false;
		while(!$done) {
			$n = strpos($this->errors,"\n",$p);
			if($n === false) {
				$done = true;
				$n = strlen($this->errors);
			}
			if($n-$p > 1) {
				$line = substr($this->errors,$p,$p-$n-1);
				[$time,$message] = explode(' ',$line,2);
				$message = stripcslashes($message);
				yield $time => $message;
			}
			$p = $n+1;
		}
	}

	public function addError(int $time, string $msg): void
	{
		$this->errors = date('c',$time).' '.addcslashes($msg,"\r\n\\")."\n".$this->errors;
	}

	public function getUserLanguage(): string
	{
		return $this->userLanguage;
	}

	public function persist(): void
	{
		if($this->status === self::STATE_DRAFT) {
			return;
		}
		$row = [
			'provider' => $this->provider->getName(),
			'provider_id' => $this->getProviderId(),
			'status' => $this->status,
			'user_status' => $this->userStatus,
			'crdate' => $this->crdate->format('Y-m-d H:i:s'),
			'errors' => $this->errors ?: null,
			'user_language' => $this->userLanguage,
		];
		if($this->completionDate) {
			$row['completion_date'] = $this->completionDate->format('Y-m-d H:i:s');
		}
		if($this->order) {
			$row['order_type'] = get_class($this->order);
			$row['order'] = json_encode($this->order);
		}
		if($this->id) {
			db_update_data('ko_payment_transaction','WHERE id='.$this->id,$row);
		} else {
			$this->id = db_insert_data('ko_payment_transaction',$row);
		}
	}
}
