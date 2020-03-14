<?PHP

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;

require_once('../inc/swiftmailer/swift_required.php');

class CommonContext extends MinkContext implements Context
{
	public $config;

	public $mails;

	public $download_url;
	public $downloaded_file;

	public function __construct($base_dir, $mail, $login, $koolencryptionkey) {
		$this->config['base_dir'] = $base_dir;
		$this->config['mail'] = [
			'host' => $mail[0],
			'username' => $mail[1],
			'password' => $mail[2]
		];
		$this->config['login'] = [
			'userid' => $login[0],
			'username' => $login[1],
			'password' => $login[2],
		];
		$this->config['KOOL_ENCRYPTION_KEY'] = $koolencryptionkey;
	}

	/**
	 * @Given make a screenshot
	 */
	public function makeAScreenshot() {
		$this->saveScreenshot(null, "screenshots");
	}

	/**
	 * @When /^(?:|I )load new mails$/
	 * @throws Exception
	 */
	public function iLoadNewMails() {
		$host = $this->config['mail']['host'];
		$user = $this->config['mail']['username'];
		$pass = $this->config['mail']['password'];
		$inbox = imap_open('{'."$host:110/pop3/tls/novalidate-cert"."}INBOX",$user,$pass) or die('Cannot connect to Mailbox: ' . imap_last_error());
		$emails = imap_search($inbox, 'ALL');

		if($emails) {
			rsort($emails);

			foreach($emails as $email_number) {
				$overview = imap_fetch_overview($inbox,$email_number,0);
				$message = imap_fetchbody($inbox,$email_number,2);
				$structure = imap_fetchstructure($inbox,$email_number);
				$mail = [
					'subject' => $overview[0]->subject,
					'from' => $overview[0]->from,
					'to' => $overview[0]->to,
					'date' => $overview[0]->date,
					'message' => $message
				];

				$attachments = array();
				if(isset($structure->parts) && count($structure->parts)) {
					for($i = 0; $i < count($structure->parts); $i++) {
						$attachments[$i] = array(
							'is_attachment' => false,
							'filename' => '',
							'name' => '',
							'attachment' => '');

						if($structure->parts[$i]->ifdparameters) {
							foreach($structure->parts[$i]->dparameters as $object) {
								if(strtolower($object->attribute) == 'filename') {
									$attachments[$i]['is_attachment'] = true;
									$attachments[$i]['filename'] = $object->value;
								}
							}
						}

						if($structure->parts[$i]->ifparameters) {
							foreach($structure->parts[$i]->parameters as $object) {
								if(strtolower($object->attribute) == 'name') {
									$attachments[$i]['is_attachment'] = true;
									$attachments[$i]['name'] = $object->value;
								}
							}
						}

						if($attachments[$i]['is_attachment']) {
							$attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
							if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
								$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
							}
							elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
								$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
							}
						}
					}
				}

				if(count($attachments)!=0){
					foreach($attachments as $at){
						if($at['is_attachment']==1 && !empty($at['filename'])){
							file_put_contents('./temp/' . $at['filename'], $at['attachment']);
						}
					}
					$mail['attachments'] = $attachments;
				}

				$this->mails[] = $mail;
				imap_delete($inbox, $overview[0]->msgno);
			}
		} else {
			throw new Exception("No mails found in mailbox\n");
		}

		imap_close($inbox, CL_EXPUNGE);
	}


	/**
	 * @When /^(?:|I )find mail with Subject "(?P<subject>[^"]+)"$/
	 * @throws Exception
	 */
	public function iFindNewMails($subject) {
		$found = FALSE;
		foreach ($this->mails AS $mail) {
			if (stristr($mail['subject'],$subject)) {
				$found = TRUE;
			}
		}

		if ($found === FALSE) {
			throw new Exception("Could not find email with Subject $subject\n");
		}
	}

	/**
	 * @When /^(?:|I )find mail with attachment "(?P<filename>[^"]+)"$/
	 * @throws Exception
	 */
	public function iFindNewMailsWithAttachment($filename) {
		$found = FALSE;
		foreach ($this->mails AS $mail) {
			foreach($mail['attachments'] AS $attachment) {
				if (stristr($attachment['filename'],$filename)) {
					$found = TRUE;
				}
			}
		}

		if ($found === FALSE) {
			throw new Exception("Could not find email with attachment\n");
		}
	}

	/**
	 * @When /^(?:|I )download the file$/
	 */
	public function iDownloadFile() {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $this->download_url,
			CURLOPT_USERAGENT => 'Behat cURL Request'
		));

		$response = curl_exec($curl);
		curl_close($curl);
		$this->downloaded_file = $response;
	}

	/**
	 * @Then /^(?:|I )open the file and find "(?P<text>[^"]+)"$/
	 * @param $text
	 * @throws Exception
	 */
	public function iOpenFileAndFind($text) {
		if (strstr($this->downloaded_file, $text) === FALSE) {
			throw new Exception("Could not find $text in file from $this->download_url\n");
		}
	}

	/**
	 * @Then /^the file is not there$/
	 * @throws Exception
	 */
	public function theFileIsNotThere() {
		if (!empty($this->downloaded_file)) {
			throw new Exception("File with content downloaded from $this->download_url\n");
		}
	}

	/**
	 * @Then I confirm the popup
	* @throws Exception
	*/
	public function iConfirmThePopup()
	{
		$this->getSession()->getDriver()->executeScript('window.confirm = function(){return true;}');
	}

	/**
	 * @When I accept confirmation dialogs
	 * @throws Exception
	 */
	public function acceptConfirmation() {
		$this->getSession()->getDriver()->executeScript('window.confirm = function(){return true;}');
	}

	/**
	 * @When I do not accept confirmation dialogs
	 * @throws Exception
	 */
	public function acceptNotConfirmation() {
		$this->getSession()->getDriver()->executeScript('window.confirm = function(){return false;}');
	}
}