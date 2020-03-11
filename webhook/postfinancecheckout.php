<?php

$ko_path = '../';
$ko_menu_akt = '';

ob_start();  //Ausgabe-Pufferung starten

include($ko_path . "inc/ko.inc");

//Redirect to SSL if needed
ko_check_ssl();

$postedContent = file_get_contents('php://input');

function reportError($message,$status,$exception = null,$files = []) {
	global $postedContent;
	$body = implode("\n\n",array_filter([$message,$exception]));
	if($_GET) {
		$files[] = new Swift_Attachment(json_encode($_GET),'GET','application/json');
	}
	if($_POST) {
		$files[] = new Swift_Attachment(json_encode($_POST),'POST','application/json');
	}
	if($postedContent) {
		$files[] = new Swift_Attachment($postedContent,'BODY',$_SERVER['CONTENT_TYPE']);
	}
	ko_send_mail(null,'reports@laupercomputing.ch','Error in PostFinanceCheckout Webhook',$body,$files);
	ob_end_clean();
	http_response_code($status);
	header('Content-Type: text/plain');
	echo $message ?: 'an error occured';
	exit;
}

$data = json_decode($postedContent,true);
if(!$data) {
	reportError('couldn\'t parse request body',400);
}

if($data['listenerEntityTechnicalName'] !== 'Transaction') {
	reportError('can only process transactions',400);
}

$providerMode = null;
foreach($PAYMENT_PROVIDER_CONFIG['PostFinanceCheckout'] as $name => $config) {
	if($config['space'] == $data['spaceId']) {
		$providerMode = $name;
		break;
	}
}

if(!$providerMode) {
	reportError('spaceId does not match configured value',400);
}

try {
	\kOOL\Payment\TransactionFactory::setTestMode($providerMode != 'prod');

	$transaction = \kOOL\Payment\TransactionFactory::findByProviderId($data['entityId']);
	if(!($transaction instanceof \kOOL\Payment\PostFinanceCheckout\Transaction)) {
		reportError('this is no PostFinanceCheckout transaction',400);
	}

	$_GET['set_lang'] = $transaction->getUserLanguage();
	include $BASE_PATH.'inc/lang.inc';

	$transaction->fetch();
	$transaction->persist();
} catch(\Throwable $e) {
	try {
		$files = [];
		if(!empty($data['entityId'])) {
			$provider = \kOOL\Payment\TransactionFactory::getActiveProvider('PostFinanceCheckout');
			$response = $provider->get('/transaction/read',[
				'spaceId' => $data['spaceId'],
				'id' => $data['entityId'],
			],false);
			if($response) {
				if($e instanceof \kOOL\Payment\TransactionNotFoundException && $decoded = json_decode($response,true)) {
					if($decoded['state'] == 'FAILED' && $decoded['confirmedOn'] === null) {
						// don't report failed (timed out) transactions that have never been confirmed
						$e = null;
					}
				}
				$files[] = new Swift_Attachment($response,'transaction.json','application/json');
			}
		}

		if($e) {
			reportError('',500,$e,$files);
		}
	} catch(\Throwable $ex) {
		reportError('',500,$ex);
	}
}
