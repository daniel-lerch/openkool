<?php
namespace kOOL\Payment\PostFinanceCheckout;

class Transaction extends \kOOL\Payment\AbstractTransaction implements \kOOL\Payment\JSPaymentFormInterface
{
	protected $version;
	protected $cleanModel = null;
	protected $paymentMethod;

	public function getPaymentFormJS(): string
	{
		$this->updateOrCreateIfNeeded();
		$url = $this->provider->get('/transaction-iframe/javascript-url',[
			'spaceId' => $this->provider->getSpaceId(),
			'id' => $this->providerId,
		],false);
		ob_start();
		include __DIR__.'/form.js';
		return ob_get_clean();
	}

	public function getPaymentMethods(): array
	{
		$this->updateOrCreateIfNeeded();
		$response = $this->provider->get('/transaction/fetchPossiblePaymentMethods',[
			'spaceId' => $this->provider->getSpaceId(),
			'id' => $this->providerId,
		]);
		return array_map(function($config) { return new PaymentMethod($config); }, $response);
	}

	public function getPaymentMethod(): ?string
	{
		return $this->paymentMethod;
	}

	public function confirm(): void
	{
		$this->updateIfNeeded();
		$model = $this->buildModel();
		$model['id'] = $this->providerId;
		$model['version'] = $this->version+1;

		$response = $this->provider->post('/transaction/confirm',$model,[
			'spaceId' => $this->provider->getSpaceId(),
		]);
		$this->updateFromResponse($response);
		$this->updateCleanModel($model);
		$this->persist();
	}

	public function fetch(): void
	{
		if(!$this->providerId) {
			throw new \RuntimeException('This transaction has not beed created on the remote server. No remote id is known.');
		}
		$response = $this->provider->get('/transaction/read',[
			'spaceId' => $this->provider->getSpaceId(),
			'id' => $this->providerId,
		]);
		if($response['version'] != $this->version) {
			$this->cleanModel = null;
		}
		$this->updateFromResponse($response);
	}

	protected function updateOrCreateIfNeeded() {
		if(!$this->createIfNeeded()) {
			$this->updateIfNeeded();
		}
	}

	protected function updateIfNeeded()
	{
		$model = $this->buildModel();
		if(sha1(serialize($model)) === $this->cleanModel) {
			return;
		}

		assert($this->getStatus() == self::STATE_DRAFT || $this->getStatus() == self::STATE_PENDING);
		$model['version'] = $this->version+1;
		$model['id'] = $this->providerId;
		try {
			$response = $this->provider->post('/transaction/update',$model,[
				'spaceId' => $this->provider->getSpaceId(),
			]);
			$this->updateFromResponse($response);
			$this->updateCleanModel($model);
		} catch(ModelVersionConflictException $ex) {
			$this->fetch();
			$this->updateIfNeeded();
		}
	}

	protected function createIfNeeded() {
		if(!$this->providerId) {
			$this->create();
			return true;
		}
		return false;
	}

	public function create() {
		assert($this->getStatus() == self::STATE_DRAFT);
		$this->providerId = null;
		$this->version = null;
		$model = $this->buildModel();
		$response = $this->provider->post('/transaction/create',$model,[
			'spaceId' => $this->provider->getSpaceId(),
		]);
		if(empty($response['id'])) {
			throw new \Exception('response did not contain a transaction id');
		}
		$this->providerId = $response['id'];
		$this->updateFromResponse($response);
		$this->updateCleanModel($model);
	}

	private function updateFromResponse($response)
	{
		if(empty($response['version'])) {
			throw new \Exception('response did not contain the version');
		}
		$this->version = $response['version'];
		if(!empty($response['completedOn'])) {
			$this->completionDate = new \DateTimeImmutable($response['completedOn']);
		}
		if(isset($response['paymentConnectorConfiguration'])) {
			$this->paymentMethod = $response['paymentConnectorConfiguration']['paymentMethodConfiguration']['name']
				?? $response['paymentConnectorConfiguration']['name'];
		}
		if(!empty($response['state'])) {
			$this->setStatus($this->provider->mapStatus($response['state']));
		}
		$this->persist();
	}

	private function updateCleanModel($model)
	{
		$this->cleanModel = sha1(serialize($model));
	}

	private function buildModel(): array
	{
		$model = [
			'currency' => 'CHF',
			'language' => $this->userLanguage,
		];
		if($this->order) {
			$person = $this->order->getPersonData();
			if($person) {
				$model['billingAddress'] = [
					'emailAddress' => $person['email'],
					'givenName' => $person['vorname'],
					'familyName' => $person['nachname'],
					'street' => $person['adresse'],
					'postCode' => $person['plz'],
					'city' => $person['ort'],
					'country' => $person['land'],
				];
			}
			foreach($this->order->getItems() as $item) {
				$lineItem = [
					'amountIncludingTax' => $item->getAmount(),
					'name' => $item->getName(),
					'quantity' => $item->getQuantity(),
					'type' => $this->provider->mapItemType($item->getType()),
					'uniqueId' => $item->getUniqueId(),
				];
				if($item->needsShipping() !== null) {
					$lineItem['shippingRequired'] = $item->needsShipping();
				}
				if($item->getVatRate() !== null) {
					$lineItem['taxes'] = ['rate' => $item->getVATRate(),'title' => 'MwSt'];
				}
				$model['lineItems'][] = $lineItem;
			}
			if($this->order instanceof \kOOL\Payment\RedirectUrlInterface) {
				$model['successUrl'] = $this->order->getSuccessUrl($this);
				$model['failedUrl'] = $this->order->getFailedUrl($this);
			}
		}
		return $model;
	}
}
