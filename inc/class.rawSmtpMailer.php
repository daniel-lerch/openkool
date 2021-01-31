<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @author Andreas Hess (Lauper Computing)
 * @copyright 2012 - 2014 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * PHPMailer - PHP email creation and transport class.
 * @package PHPMailer
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 */
class RawSmtpMailer
{

    /**
     * The character set of the message.
     * @type string
     */
    public $CharSet = 'iso-8859-1';


    /**
     * Holds the most recent mailer error message.
     * @type string
     */
    public $ErrorInfo = '';

    /**
     * The From name of the message.
     * @type string
     */
    public $FromName = 'Root User';

    /**
     * The Sender email (Return-Path) of the message.
     * If not empty, will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
     * @type string
     */
    public $Sender = '';

    /**
     * The complete compiled MIME message.
     * @access protected
     * @type string
     */
    protected $rawMessage;

    /**
     * The hostname to use in Message-Id and Received headers
     * and as default HELO string.
     * If empty, the value returned
     * by SERVER_NAME is used or 'localhost.localdomain'.
     * @type string
     */
    public $Hostname = '';

    /**
     * SMTP hosts.
     * Either a single hostname or multiple semicolon-delimited hostnames.
     * You can also specify a different port
     * for each host by using this format: [hostname:port]
     * (e.g. "smtp1.example.com:25;smtp2.example.com").
     * Hosts will be tried in order.
     * @type string
     */
    public $Host = 'localhost';

    /**
     * The default SMTP server port.
     * @type integer
     * @TODO Why is this needed when the SMTP class takes care of it?
     */
    public $Port = 25;

    /**
     * The SMTP HELO of the message.
     * Default is $Hostname.
     * @type string
     * @see PHPMailer::$Hostname
     */
    public $Helo = '';

    /**
     * The secure connection prefix.
     * Options: "", "ssl" or "tls"
     * @type string
     */
    public $SMTPSecure = '';

    /**
     * Whether to use SMTP authentication.
     * Uses the Username and Password properties.
     * @type boolean
     * @see PHPMailer::$Username
     * @see PHPMailer::$Password
     */
    public $SMTPAuth = false;

    /**
     * SMTP username.
     * @type string
     */
    public $Username = '';

    /**
     * SMTP password.
     * @type string
     */
    public $Password = '';

    /**
     * SMTP auth type.
     * Options are LOGIN (default), PLAIN, NTLM, CRAM-MD5
     * @type string
     */
    public $AuthType = '';

    /**
     * SMTP realm.
     * Used for NTLM auth
     * @type string
     */
    public $Realm = '';

    /**
     * SMTP workstation.
     * Used for NTLM auth
     * @type string
     */
    public $Workstation = '';

    /**
     * The SMTP server timeout in seconds.
     * @type integer
     */
    public $Timeout = 10;

    /**
     * SMTP class debug output mode.
     * Options:
     *   0: no output
     *   1: commands
     *   2: data and commands
     *   3: as 2 plus connection status
     *   4: low level data output
     * @type integer
     * @see SMTP::$do_debug
     */
    public $SMTPDebug = 0;

    /**
     * How to handle debug output.
     * Options:
     *   'echo': Output plain-text as-is, appropriate for CLI
     *   'html': Output escaped, line breaks converted to <br>, appropriate for browser output
     *   'error_log': Output to error log as configured in php.ini
     * @type string
     * @see SMTP::$Debugoutput
     */
    public $Debugoutput = 'echo';

    /**
     * Whether to keep SMTP connection open after each message.
     * If this is set to true then to close the connection
     * requires an explicit call to smtpClose().
     * @type boolean
     */
    public $SMTPKeepAlive = false;

    /**
     * Whether to generate VERP addresses on send.
     * Only applicable when sending via SMTP.
     * @link http://en.wikipedia.org/wiki/Variable_envelope_return_path
     * @link http://www.postfix.org/VERP_README.html Postfix VERP info
     * @type boolean
     */
    public $do_verp = false;

    /**
     * The default line ending.
     * @note The default remains "\n". We force CRLF where we know
     *        it must be used via self::CRLF.
     * @type string
     */
    public $LE = "\n";

    /**
     * An instance of the SMTP sender class.
     * @type SMTP
     * @access protected
     */
    protected $smtp = null;

    /**
     * The array of 'to' addresses.
     * @type array
     * @access protected
     */
    protected $to = array();

    /**
     * An array of all kinds of addresses.
     * Includes all of $to, $cc, $bcc, $replyto
     * @type array
     * @access protected
     */
    protected $all_recipients = array();

    /**
     * The most recent Message-ID (including angular brackets).
     * @type string
     * @access protected
     */
    protected $lastMessageID = '';

    /**
     * The array of available languages.
     * @type array
     * @access protected
     */
    protected $language = array();

    /**
     * The number of errors encountered.
     * @type integer
     * @access protected
     */
    protected $error_count = 0;

    /**
     * Whether to throw exceptions for errors.
     * @type boolean
     * @access protected
     */
    protected $exceptions = false;

    /**
     * Error severity: message only, continue processing
     */
    const STOP_MESSAGE = 0;

    /**
     * Error severity: message, likely ok to continue processing
     */
    const STOP_CONTINUE = 1;

    /**
     * Error severity: message, plus full stop, critical error reached
     */
    const STOP_CRITICAL = 2;

    /**
     * Constructor
     * @param boolean $exceptions Should we throw external exceptions?
     */
    public function __construct($exceptions = false) {
			global $MAIL_TRANSPORT;

			$this->exceptions = ($exceptions == true);
			$this->Host = $MAIL_TRANSPORT['host'];
			$this->Port = $MAIL_TRANSPORT['port'];
			$this->SMTPAuth = ($MAIL_TRANSPORT['auth_user'] && $MAIL_TRANSPORT['auth_pass']);
			$this->SMTPSecure = ($MAIL_TRANSPORT['tls'] ? 'tls' : ($MAIL_TRANSPORT['ssl'] ? 'ssl' : ""));
			if($MAIL_TRANSPORT['auth_user'] && $MAIL_TRANSPORT['auth_pass']) {
				$this->Username = $MAIL_TRANSPORT['auth_user'];
				$this->Password = $MAIL_TRANSPORT['auth_pass'];
			}
			if (isset($MAIL_TRANSPORT['sender'])) {
				$this->Sender = $MAIL_TRANSPORT['sender'];
			}
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
    	$this->smtpClose();
    }


    /**
     * Output debugging info via user-defined method.
     * Only if debug output is enabled.
     * @see PHPMailer::$Debugoutput
     * @see PHPMailer::$SMTPDebug
     * @param string $str
     */
    protected function edebug($str)
    {
        if (!$this->SMTPDebug) {
            return;
        }
        switch ($this->Debugoutput) {
            case 'error_log':
                error_log($str);
                break;
            case 'html':
                //Cleans up output a bit for a better looking display that's HTML-safe
                echo htmlentities(preg_replace('/[\r\n]+/', '', $str), ENT_QUOTES, $this->CharSet) . "<br>\n";
                break;
            case 'echo':
            default:
                echo $str."\n";
        }
    }


    /**
     * Add a "To" address.
     * @param string $address
     * @param string $name
     * @return boolean true on success, false if address already used
     */
    public function addAddress($address, $name = '')
    {
		array_push($this->to, array($address, $name));
		$this->all_recipients[strtolower($address)] = true;
    }

	/**
	 * Add a "To" address.
	 * @param string $address
	 * @param string $name
	 * @return boolean true on success, false if address already used
	 */
	public function removeAddresses()
	{
		$this->to = array();
		$this->all_recipients = array();
	}


    /**
     * Set the Sender properties.
     * @param string $address
     * @throws Exception
     * @return boolean
     */
    public function setSender($address)
    {
		if (empty($this->Sender)) {
			$this->Sender = trim($address);
			return true;
		} else {
			return false;
		}
    }

    /**
     * Return the Message-ID header of the last email.
     * Technically this is the value from the last time the headers were created,
     * but it's also the message ID of the last sent message except in
     * pathological cases.
     * @return string
     */
    public function getLastMessageID()
    {
        return $this->lastMessageID;
    }

    /**
     * Create a message and send it.
     * Uses the sending method specified by $Mailer.
     * @throws Exception
     * @return boolean false on error - See the ErrorInfo property for details of the error.
     */
    public function send()
    {
        try {
			return $this->smtpSend($this->rawMessage);
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }



    /**
     * Get an instance to use for SMTP operations.
     * Override this function to load your own SMTP implementation
     * @return SMTP
     */
    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new SMTP;
        }
        return $this->smtp;
    }

    /**
     * Send mail via SMTP.
     * Returns false if there is a bad MAIL FROM, RCPT, or DATA input.
     * Uses the PHPMailerSMTP class by default.
     * @see PHPMailer::getSMTPInstance() to use a different class.
     * @param string $header The message headers
     * @param string $body The message body
     * @throws Exception
     * @uses SMTP
     * @access protected
     * @return boolean
     */
    protected function smtpSend($message)
    {
        $bad_rcpt = array();

        if (!$this->smtpConnect()) {
            throw new Exception($this->lang('smtp_connect_failed'), self::STOP_CRITICAL);
        }
        $smtp_from = $this->Sender;
        if (!$this->smtp->mail($smtp_from)) {
            $this->setError($this->lang('from_failed') . $smtp_from . ' : ' . implode(',', $this->smtp->getError()));
            throw new Exception($this->ErrorInfo, self::STOP_CRITICAL);
        }

        // Attempt to send to all recipients
        foreach ($this->to as $to) {
            if (!$this->smtp->recipient($to[0])) {
                $bad_rcpt[] = $to[0];
            } else {
            }
        }

        // Only send the DATA command if we have viable recipients
        if ((count($this->all_recipients) > count($bad_rcpt)) and !$this->smtp->data($message)) {
            throw new Exception($this->lang('data_not_accepted'), self::STOP_CRITICAL);
        }
        if ($this->SMTPKeepAlive == true) {
            $this->smtp->reset();
        } else {
            $this->smtp->quit();
            $this->smtp->close();
        }
        if (count($bad_rcpt) > 0) { // Create error message for any bad addresses
            throw new Exception(
                $this->lang('recipients_failed') . implode(', ', $bad_rcpt),
                self::STOP_CONTINUE
            );
        }
        return true;
    }

    /**
     * Initiate a connection to an SMTP server.
     * Returns false if the operation failed.
     * @param array $options An array of options compatible with stream_context_create()
     * @uses SMTP
     * @access public
     * @throws Exception
     * @return boolean
     */
    public function smtpConnect($options = array())
    {
        if (is_null($this->smtp)) {
            $this->smtp = $this->getSMTPInstance();
        }

        // Already connected?
        if ($this->smtp->connected()) {
            return true;
        }

        $this->smtp->setTimeout($this->Timeout);
        $this->smtp->setDebugLevel($this->SMTPDebug);
        $this->smtp->setDebugOutput($this->Debugoutput);
        $this->smtp->setVerp($this->do_verp);
        $hosts = explode(';', $this->Host);
        $lastexception = null;

        foreach ($hosts as $hostentry) {
            $hostinfo = array();
            if (!preg_match('/^((ssl|tls):\/\/)*([a-zA-Z0-9\.-]*):?([0-9]*)$/', trim($hostentry), $hostinfo)) {
                // Not a valid host entry
                continue;
            }
            // $hostinfo[2]: optional ssl or tls prefix
            // $hostinfo[3]: the hostname
            // $hostinfo[4]: optional port number
            // The host string prefix can temporarily override the current setting for SMTPSecure
            // If it's not specified, the default value is used
            $prefix = '';
            $tls = ($this->SMTPSecure == 'tls');
            if ($hostinfo[2] == 'ssl' or ($hostinfo[2] == '' and $this->SMTPSecure == 'ssl')) {
                $prefix = 'ssl://';
                $tls = false; // Can't have SSL and TLS at once
            } elseif ($hostinfo[2] == 'tls') {
                $tls = true;
                // tls doesn't use a prefix
            }
            $host = $hostinfo[3];
            $port = $this->Port;
            $tport = (integer)$hostinfo[4];
            if ($tport > 0 and $tport < 65536) {
                $port = $tport;
            }
            if ($this->smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
                try {
                    if ($this->Helo) {
                        $hello = $this->Helo;
                    } else {
                        $hello = $this->serverHostname();
                    }
                    $this->smtp->hello($hello);

                    if ($tls) {
                        if (!$this->smtp->startTLS()) {
                            throw new Exception($this->lang('connect_host'));
                        }
                        // We must resend HELO after tls negotiation
                        $this->smtp->hello($hello);
                    }
                    if ($this->SMTPAuth) {
                        if (!$this->smtp->authenticate(
                            $this->Username,
                            $this->Password,
                            $this->AuthType,
                            $this->Realm,
                            $this->Workstation
                        )
                        ) {
                            throw new Exception($this->lang('authenticate'));
                        }
                    }
                    return true;
                } catch (Exception $exc) {
                    $lastexception = $exc;
                    // We must have connected, but then failed TLS or Auth, so close connection nicely
                    $this->smtp->quit();
                }
            }
        }
        // If we get here, all connection attempts have failed, so close connection hard
        $this->smtp->close();
        // As we've caught all exceptions, just report whatever the last one was
        if ($this->exceptions and !is_null($lastexception)) {
            throw $lastexception;
        }
        return false;
    }

    /**
     * Close the active SMTP session if one exists.
     * @return void
     */
    public function smtpClose()
    {
        if ($this->smtp !== null) {
            if ($this->smtp->connected()) {
                $this->smtp->quit();
                $this->smtp->close();
            }
        }
    }

    /**
     * Set the language for error messages.
     * Returns false if it cannot load the language file.
     * The default language is English.
     * @param string $langcode ISO 639-1 2-character language code (e.g. French is "fr")
     * @param string $lang_path Path to the language file directory, with trailing separator (slash)
     * @return boolean
     * @access public
     */
    public function setLanguage($langcode = 'en', $lang_path = '')
    {
        // Define full set of translatable strings in English
        $this->language = array(
            'authenticate' => 'SMTP Error: Could not authenticate.',
            'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
            'data_not_accepted' => 'SMTP Error: data not accepted.',
            'empty_message' => 'Message body empty',
            'encoding' => 'Unknown encoding: ',
            'execute' => 'Could not execute: ',
            'file_access' => 'Could not access file: ',
            'file_open' => 'File Error: Could not open file: ',
            'from_failed' => 'The following From address failed: ',
            'instantiate' => 'Could not instantiate mail function.',
            'invalid_address' => 'Invalid address',
            'mailer_not_supported' => ' mailer is not supported.',
            'provide_address' => 'You must provide at least one recipient email address.',
            'recipients_failed' => 'SMTP Error: The following recipients failed: ',
            'signing' => 'Signing Error: ',
            'smtp_connect_failed' => 'SMTP connect() failed.',
            'smtp_error' => 'SMTP server error: ',
            'variable_set' => 'Cannot set or reset variable: '
        );
    }

    /**
     * Get the array of strings for the current language.
     * @return array
     */
    public function getTranslations()
    {
        return $this->language;
    }


    /**
     * Encode a string in requested format.
     * Returns an empty string on failure.
     * @param string $str The text to encode
     * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
     * @access public
     * @return string
     */
    public function encodeString($str, $encoding = 'base64')
    {
        $encoded = '';
        switch (strtolower($encoding)) {
            case 'base64':
                $encoded = chunk_split(base64_encode($str), 76, $this->LE);
                break;
            case '7bit':
            case '8bit':
                $encoded = $this->fixEOL($str);
                // Make sure it ends with a line break
                if (substr($encoded, -(strlen($this->LE))) != $this->LE) {
                    $encoded .= $this->LE;
                }
                break;
            case 'binary':
                $encoded = $str;
                break;
            case 'quoted-printable':
                $encoded = $this->encodeQP($str);
                break;
            default:
                $this->setError($this->lang('encoding') . $encoding);
                break;
        }
        return $encoded;
    }



    /**
     * Encode a string in quoted-printable format.
     * According to RFC2045 section 6.7.
     * @access public
     * @param string $string The text to encode
     * @param integer $line_max Number of chars allowed on a line before wrapping
     * @return string
     * @link http://www.php.net/manual/en/function.quoted-printable-decode.php#89417 Adapted from this comment
     */
    public function encodeQP($string, $line_max = 76)
    {
        if (function_exists('quoted_printable_encode')) { // Use native function if it's available (>= PHP5.3)
            return $this->fixEOL(quoted_printable_encode($string));
        }
        // Fall back to a pure PHP implementation
        $string = str_replace(
            array('%20', '%0D%0A.', '%0D%0A', '%'),
            array(' ', "\r\n=2E", "\r\n", '='),
            rawurlencode($string)
        );
        $string = preg_replace('/[^\r\n]{' . ($line_max - 3) . '}[^=\r\n]{2}/', "$0=\r\n", $string);
        return $this->fixEOL($string);
    }




    /**
     * Add an error message to the error container.
     * @access protected
     * @param string $msg
     * @return void
     */
    protected function setError($msg)
    {
        $this->error_count++;
        if (!is_null($this->smtp)) {
            $lasterror = $this->smtp->getError();
            if (!empty($lasterror) and array_key_exists('smtp_msg', $lasterror)) {
                $msg .= '<p>' . $this->lang('smtp_error') . $lasterror['smtp_msg'] . "</p>\n";
            }
        }
        $this->ErrorInfo = $msg;
    }


    /**
     * Get the server hostname.
     * Returns 'localhost.localdomain' if unknown.
     * @access protected
     * @return string
     */
    public function serverHostname()
    {
        $result = 'localhost.localdomain';
        if (!empty($this->Hostname)) {
            $result = $this->Hostname;
        } elseif (isset($_SERVER) and array_key_exists('SERVER_NAME', $_SERVER) and !empty($_SERVER['SERVER_NAME'])) {
            $result = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $result = gethostname();
        } elseif (php_uname('n') !== false) {
            $result = php_uname('n');
        }
        return $result;
    }

    /**
     * Get an error message in the current language.
     * @access protected
     * @param string $key
     * @return string
     */
    protected function lang($key)
    {
        if (count($this->language) < 1) {
            $this->setLanguage('en'); // set the default language
        }

        if (isset($this->language[$key])) {
            return $this->language[$key];
        } else {
            return 'Language string failed to load: ' . $key;
        }
    }

    /**
     * Check if an error occurred.
     * @access public
     * @return boolean True if an error did occur.
     */
    public function isError()
    {
        return ($this->error_count > 0);
    }

    /**
     * Ensure consistent line endings in a string.
     * Changes every end of line from CRLF, CR or LF to $this->LE.
     * @access public
     * @param string $str String to fixEOL
     * @return string
     */
    public function fixEOL($str)
    {
        // Normalise to \n
        $nstr = str_replace(array("\r\n", "\r"), "\n", $str);
        // Now convert LE as needed
        if ($this->LE !== "\n") {
            $nstr = str_replace("\n", $this->LE, $nstr);
        }
        return $nstr;
    }


	public function setMessage($rawMessage) {
		$this->rawMessage = $rawMessage;
	}


}











/**
 * PHPMailer RFC821 SMTP email transport class.
 * PHP Version 5
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2014 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * PHPMailer RFC821 SMTP email transport class.
 * Implements RFC 821 SMTP commands and provides some utility methods for sending mail to an SMTP server.
 * @package PHPMailer
 * @author Chris Ryan <unknown@example.com>
 * @author Marcus Bointon <phpmailer@synchromedia.co.uk>
 */
class SMTP
{
	/**
	 * The PHPMailer SMTP version number.
	 * @type string
	 */
	const VERSION = '5.2.8';

	/**
	 * SMTP line break constant.
	 * @type string
	 */
	const CRLF = "\r\n";

	/**
	 * The SMTP port to use if one is not specified.
	 * @type integer
	 */
	const DEFAULT_SMTP_PORT = 25;

	/**
	 * The maximum line length allowed by RFC 2822 section 2.1.1
	 * @type integer
	 */
	const MAX_LINE_LENGTH = 998;

	/**
	 * The PHPMailer SMTP Version number.
	 * @type string
	 * @deprecated Use the constant instead
	 * @see SMTP::VERSION
	 */
	public $Version = '5.2.8';

	/**
	 * SMTP server port number.
	 * @type integer
	 * @deprecated This is only ever used as a default value, so use the constant instead
	 * @see SMTP::DEFAULT_SMTP_PORT
	 */
	public $SMTP_PORT = 25;

	/**
	 * SMTP reply line ending.
	 * @type string
	 * @deprecated Use the constant instead
	 * @see SMTP::CRLF
	 */
	public $CRLF = "\r\n";

	/**
	 * Debug output level.
	 * Options:
	 * * `0` No output
	 * * `1` Commands
	 * * `2` Data and commands
	 * * `3` As 2 plus connection status
	 * * `4` Low-level data output
	 * @type integer
	 */
	public $do_debug = 0;

	/**
	 * How to handle debug output.
	 * Options:
	 * * `echo` Output plain-text as-is, appropriate for CLI
	 * * `html` Output escaped, line breaks converted to <br>, appropriate for browser output
	 * * `error_log` Output to error log as configured in php.ini
	 * @type string
	 */
	public $Debugoutput = 'echo';

	/**
	 * Whether to use VERP.
	 * @link http://en.wikipedia.org/wiki/Variable_envelope_return_path
	 * @link http://www.postfix.org/VERP_README.html Info on VERP
	 * @type boolean
	 */
	public $do_verp = false;

	/**
	 * The timeout value for connection, in seconds.
	 * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2
	 * This needs to be quite high to function correctly with hosts using greetdelay as an anti-spam measure.
	 * @link http://tools.ietf.org/html/rfc2821#section-4.5.3.2
	 * @type integer
	 */
	public $Timeout = 300;

	/**
	 * The SMTP timelimit value for reads, in seconds.
	 * @type integer
	 */
	public $Timelimit = 30;

	/**
	 * The socket for the server connection.
	 * @type resource
	 */
	protected $smtp_conn;

	/**
	 * Error message, if any, for the last call.
	 * @type array
	 */
	protected $error = array();

	/**
	 * The reply the server sent to us for HELO.
	 * If null, no HELO string has yet been received.
	 * @type string|null
	 */
	protected $helo_rply = null;

	/**
	 * The most recent reply received from the server.
	 * @type string
	 */
	protected $last_reply = '';

	/**
	 * Output debugging info via a user-selected method.
	 * @param string $str Debug string to output
	 * @return void
	 */
	protected function edebug($str)
	{
		switch ($this->Debugoutput) {
			case 'error_log':
				//Don't output, just log
				error_log($str);
				break;
			case 'html':
				//Cleans up output a bit for a better looking, HTML-safe output
				echo htmlentities(
						preg_replace('/[\r\n]+/', '', $str),
						ENT_QUOTES,
						'UTF-8'
					)
					. "<br>\n";
				break;
			case 'echo':
			default:
				echo gmdate('Y-m-d H:i:s')."\t".trim($str)."\n";
		}
	}

	/**
	 * Connect to an SMTP server.
	 * @param string $host SMTP server IP or host name
	 * @param integer $port The port number to connect to
	 * @param integer $timeout How long to wait for the connection to open
	 * @param array $options An array of options for stream_context_create()
	 * @access public
	 * @return boolean
	 */
	public function connect($host, $port = null, $timeout = 30, $options = array())
	{
		static $streamok;
		//This is enabled by default since 5.0.0 but some providers disable it
		//Check this once and cache the result
		if (is_null($streamok)) {
			$streamok = function_exists('stream_socket_client');
		}
		// Clear errors to avoid confusion
		$this->error = array();
		// Make sure we are __not__ connected
		if ($this->connected()) {
			// Already connected, generate error
			$this->error = array('error' => 'Already connected to a server');
			return false;
		}
		if (empty($port)) {
			$port = self::DEFAULT_SMTP_PORT;
		}
		// Connect to the SMTP server
		if ($this->do_debug >= 3) {
			$this->edebug("Connection: opening to $host:$port, t=$timeout, opt=".var_export($options, true));
		}
		$errno = 0;
		$errstr = '';
		if ($streamok) {
			$socket_context = stream_context_create($options);
			//Suppress errors; connection failures are handled at a higher level
			$this->smtp_conn = @stream_socket_client(
				$host . ":" . $port,
				$errno,
				$errstr,
				$timeout,
				STREAM_CLIENT_CONNECT,
				$socket_context
			);
		} else {
			//Fall back to fsockopen which should work in more places, but is missing some features
			if ($this->do_debug >= 3) {
				$this->edebug("Connection: stream_socket_client not available, falling back to fsockopen");
			}
			$this->smtp_conn = fsockopen(
				$host,
				$port,
				$errno,
				$errstr,
				$timeout
			);
		}
		// Verify we connected properly
		if (!is_resource($this->smtp_conn)) {
			$this->error = array(
				'error' => 'Failed to connect to server',
				'errno' => $errno,
				'errstr' => $errstr
			);
			if ($this->do_debug >= 1) {
				$this->edebug(
					'SMTP ERROR: ' . $this->error['error']
					. ": $errstr ($errno)"
				);
			}
			return false;
		}
		if ($this->do_debug >= 3) {
			$this->edebug('Connection: opened');
		}
		// SMTP server can take longer to respond, give longer timeout for first read
		// Windows does not have support for this timeout function
		if (substr(PHP_OS, 0, 3) != 'WIN') {
			$max = ini_get('max_execution_time');
			if ($max != 0 && $timeout > $max) { // Don't bother if unlimited
				@set_time_limit($timeout);
			}
			stream_set_timeout($this->smtp_conn, $timeout, 0);
		}
		// Get any announcement
		$announce = $this->get_lines();
		if ($this->do_debug >= 2) {
			$this->edebug('SERVER -> CLIENT: ' . $announce);
		}
		return true;
	}

	/**
	 * Initiate a TLS (encrypted) session.
	 * @access public
	 * @return boolean
	 */
	public function startTLS()
	{
		if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
			return false;
		}
		// Begin encrypted connection
		if (!stream_socket_enable_crypto(
			$this->smtp_conn,
			true,
			STREAM_CRYPTO_METHOD_TLS_CLIENT
		)) {
			return false;
		}
		return true;
	}

	/**
	 * Perform SMTP authentication.
	 * Must be run after hello().
	 * @see hello()
	 * @param string $username    The user name
	 * @param string $password    The password
	 * @param string $authtype    The auth type (PLAIN, LOGIN, NTLM, CRAM-MD5)
	 * @param string $realm       The auth realm for NTLM
	 * @param string $workstation The auth workstation for NTLM
	 * @access public
	 * @return boolean True if successfully authenticated.
	 */
	public function authenticate(
		$username,
		$password,
		$authtype = 'LOGIN',
		$realm = '',
		$workstation = ''
	) {
		if (empty($authtype)) {
			$authtype = 'LOGIN';
		}
		switch ($authtype) {
			case 'PLAIN':
				// Start authentication
				if (!$this->sendCommand('AUTH', 'AUTH PLAIN', 334)) {
					return false;
				}
				// Send encoded username and password
				if (!$this->sendCommand(
					'User & Password',
					base64_encode("\0" . $username . "\0" . $password),
					235
				)
				) {
					return false;
				}
				break;
			case 'LOGIN':
				// Start authentication
				if (!$this->sendCommand('AUTH', 'AUTH LOGIN', 334)) {
					return false;
				}
				if (!$this->sendCommand("Username", base64_encode($username), 334)) {
					return false;
				}
				if (!$this->sendCommand("Password", base64_encode($password), 235)) {
					return false;
				}
				break;
			case 'NTLM':
				/*
				 * ntlm_sasl_client.php
				 * Bundled with Permission
				 *
				 * How to telnet in windows:
				 * http://technet.microsoft.com/en-us/library/aa995718%28EXCHG.65%29.aspx
				 * PROTOCOL Docs http://curl.haxx.se/rfc/ntlm.html#ntlmSmtpAuthentication
				 */
				require_once 'extras/ntlm_sasl_client.php';
				$temp = new stdClass();
				$ntlm_client = new ntlm_sasl_client_class;
				//Check that functions are available
				if (!$ntlm_client->Initialize($temp)) {
					$this->error = array('error' => $temp->error);
					if ($this->do_debug >= 1) {
						$this->edebug(
							'You need to enable some modules in your php.ini file: '
							. $this->error['error']
						);
					}
					return false;
				}
				//msg1
				$msg1 = $ntlm_client->TypeMsg1($realm, $workstation); //msg1

				if (!$this->sendCommand(
					'AUTH NTLM',
					'AUTH NTLM ' . base64_encode($msg1),
					334
				)
				) {
					return false;
				}
				//Though 0 based, there is a white space after the 3 digit number
				//msg2
				$challenge = substr($this->last_reply, 3);
				$challenge = base64_decode($challenge);
				$ntlm_res = $ntlm_client->NTLMResponse(
					substr($challenge, 24, 8),
					$password
				);
				//msg3
				$msg3 = $ntlm_client->TypeMsg3(
					$ntlm_res,
					$username,
					$realm,
					$workstation
				);
				// send encoded username
				return $this->sendCommand('Username', base64_encode($msg3), 235);
			case 'CRAM-MD5':
				// Start authentication
				if (!$this->sendCommand('AUTH CRAM-MD5', 'AUTH CRAM-MD5', 334)) {
					return false;
				}
				// Get the challenge
				$challenge = base64_decode(substr($this->last_reply, 4));

				// Build the response
				$response = $username . ' ' . $this->hmac($challenge, $password);

				// send encoded credentials
				return $this->sendCommand('Username', base64_encode($response), 235);
		}
		return true;
	}

	/**
	 * Calculate an MD5 HMAC hash.
	 * Works like hash_hmac('md5', $data, $key)
	 * in case that function is not available
	 * @param string $data The data to hash
	 * @param string $key  The key to hash with
	 * @access protected
	 * @return string
	 */
	protected function hmac($data, $key)
	{
		if (function_exists('hash_hmac')) {
			return hash_hmac('md5', $data, $key);
		}

		// The following borrowed from
		// http://php.net/manual/en/function.mhash.php#27225

		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		// Eliminates the need to install mhash to compute a HMAC
		// Hacked by Lance Rushing

		$bytelen = 64; // byte length for md5
		if (strlen($key) > $bytelen) {
			$key = pack('H*', md5($key));
		}
		$key = str_pad($key, $bytelen, chr(0x00));
		$ipad = str_pad('', $bytelen, chr(0x36));
		$opad = str_pad('', $bytelen, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return md5($k_opad . pack('H*', md5($k_ipad . $data)));
	}

	/**
	 * Check connection state.
	 * @access public
	 * @return boolean True if connected.
	 */
	public function connected()
	{
		if (is_resource($this->smtp_conn)) {
			$sock_status = stream_get_meta_data($this->smtp_conn);
			if ($sock_status['eof']) {
				// the socket is valid but we are not connected
				if ($this->do_debug >= 1) {
					$this->edebug(
						'SMTP NOTICE: EOF caught while checking if connected'
					);
				}
				$this->close();
				return false;
			}
			return true; // everything looks good
		}
		return false;
	}

	/**
	 * Close the socket and clean up the state of the class.
	 * Don't use this function without first trying to use QUIT.
	 * @see quit()
	 * @access public
	 * @return void
	 */
	public function close()
	{
		$this->error = array();
		$this->helo_rply = null;
		if (is_resource($this->smtp_conn)) {
			// close the connection and cleanup
			fclose($this->smtp_conn);
			if ($this->do_debug >= 3) {
				$this->edebug('Connection: closed');
			}
		}
	}

	/**
	 * Send an SMTP DATA command.
	 * Issues a data command and sends the msg_data to the server,
	 * finializing the mail transaction. $msg_data is the message
	 * that is to be send with the headers. Each header needs to be
	 * on a single line followed by a <CRLF> with the message headers
	 * and the message body being separated by and additional <CRLF>.
	 * Implements rfc 821: DATA <CRLF>
	 * @param string $msg_data Message data to send
	 * @access public
	 * @return boolean
	 */
	public function data($msg_data)
	{
		if (!$this->sendCommand('DATA', 'DATA', 354)) {
			return false;
		}
		/* The server is ready to accept data!
		 * According to rfc821 we should not send more than 1000 characters on a single line (including the CRLF)
		 * so we will break the data up into lines by \r and/or \n then if needed we will break each of those into
		 * smaller lines to fit within the limit.
		 * We will also look for lines that start with a '.' and prepend an additional '.'.
		 * NOTE: this does not count towards line-length limit.
		 */

		// Normalize line breaks before exploding
		$lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $msg_data));

		/* To distinguish between a complete RFC822 message and a plain message body, we check if the first field
		 * of the first line (':' separated) does not contain a space then it _should_ be a header and we will
		 * process all lines before a blank line as headers.
		 */

		$field = substr($lines[0], 0, strpos($lines[0], ':'));
		$in_headers = false;
		if (!empty($field) && strpos($field, ' ') === false) {
			$in_headers = true;
		}

		foreach ($lines as $line) {
			$lines_out = array();
			if ($in_headers and $line == '') {
				$in_headers = false;
			}
			// ok we need to break this line up into several smaller lines
			//This is a small micro-optimisation: isset($str[$len]) is equivalent to (strlen($str) > $len)
			while (isset($line[self::MAX_LINE_LENGTH])) {
				//Working backwards, try to find a space within the last MAX_LINE_LENGTH chars of the line to break on
				//so as to avoid breaking in the middle of a word
				$pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');
				if (!$pos) { //Deliberately matches both false and 0
					//No nice break found, add a hard break
					$pos = self::MAX_LINE_LENGTH - 1;
					$lines_out[] = substr($line, 0, $pos);
					$line = substr($line, $pos);
				} else {
					//Break at the found point
					$lines_out[] = substr($line, 0, $pos);
					//Move along by the amount we dealt with
					$line = substr($line, $pos + 1);
				}
				/* If processing headers add a LWSP-char to the front of new line
				 * RFC822 section 3.1.1
				 */
				if ($in_headers) {
					$line = "\t" . $line;
				}
			}
			$lines_out[] = $line;

			// Send the lines to the server
			foreach ($lines_out as $line_out) {
				//RFC2821 section 4.5.2
				if (!empty($line_out) and $line_out[0] == '.') {
					$line_out = '.' . $line_out;
				}
				$this->client_send($line_out . self::CRLF);
			}
		}

		// Message data has been sent, complete the command
		return $this->sendCommand('DATA END', '.', 250);
	}

	/**
	 * Send an SMTP HELO or EHLO command.
	 * Used to identify the sending server to the receiving server.
	 * This makes sure that client and server are in a known state.
	 * Implements RFC 821: HELO <SP> <domain> <CRLF>
	 * and RFC 2821 EHLO.
	 * @param string $host The host name or IP to connect to
	 * @access public
	 * @return boolean
	 */
	public function hello($host = '')
	{
		// Try extended hello first (RFC 2821)
		return (boolean)($this->sendHello('EHLO', $host) or $this->sendHello('HELO', $host));
	}

	/**
	 * Send an SMTP HELO or EHLO command.
	 * Low-level implementation used by hello()
	 * @see hello()
	 * @param string $hello The HELO string
	 * @param string $host The hostname to say we are
	 * @access protected
	 * @return boolean
	 */
	protected function sendHello($hello, $host)
	{
		$noerror = $this->sendCommand($hello, $hello . ' ' . $host, 250);
		$this->helo_rply = $this->last_reply;
		return $noerror;
	}

	/**
	 * Send an SMTP MAIL command.
	 * Starts a mail transaction from the email address specified in
	 * $from. Returns true if successful or false otherwise. If True
	 * the mail transaction is started and then one or more recipient
	 * commands may be called followed by a data command.
	 * Implements rfc 821: MAIL <SP> FROM:<reverse-path> <CRLF>
	 * @param string $from Source address of this message
	 * @access public
	 * @return boolean
	 */
	public function mail($from)
	{
		$useVerp = ($this->do_verp ? ' XVERP' : '');
		return $this->sendCommand(
			'MAIL FROM',
			'MAIL FROM:<' . $from . '>' . $useVerp,
			250
		);
	}

	/**
	 * Send an SMTP QUIT command.
	 * Closes the socket if there is no error or the $close_on_error argument is true.
	 * Implements from rfc 821: QUIT <CRLF>
	 * @param boolean $close_on_error Should the connection close if an error occurs?
	 * @access public
	 * @return boolean
	 */
	public function quit($close_on_error = true)
	{
		$noerror = $this->sendCommand('QUIT', 'QUIT', 221);
		$err = $this->error; //Save any error
		if ($noerror or $close_on_error) {
			$this->close();
			$this->error = $err; //Restore any error from the quit command
		}
		return $noerror;
	}

	/**
	 * Send an SMTP RCPT command.
	 * Sets the TO argument to $toaddr.
	 * Returns true if the recipient was accepted false if it was rejected.
	 * Implements from rfc 821: RCPT <SP> TO:<forward-path> <CRLF>
	 * @param string $toaddr The address the message is being sent to
	 * @access public
	 * @return boolean
	 */
	public function recipient($toaddr)
	{
		return $this->sendCommand(
			'RCPT TO',
			'RCPT TO:<' . $toaddr . '>',
			array(250, 251)
		);
	}

	/**
	 * Send an SMTP RSET command.
	 * Abort any transaction that is currently in progress.
	 * Implements rfc 821: RSET <CRLF>
	 * @access public
	 * @return boolean True on success.
	 */
	public function reset()
	{
		return $this->sendCommand('RSET', 'RSET', 250);
	}

	/**
	 * Send a command to an SMTP server and check its return code.
	 * @param string $command       The command name - not sent to the server
	 * @param string $commandstring The actual command to send
	 * @param integer|array $expect     One or more expected integer success codes
	 * @access protected
	 * @return boolean True on success.
	 */
	protected function sendCommand($command, $commandstring, $expect)
	{
		if (!$this->connected()) {
			$this->error = array(
				'error' => "Called $command without being connected"
			);
			return false;
		}
		$this->client_send($commandstring . self::CRLF);

		$reply = $this->get_lines();
		$code = substr($reply, 0, 3);

		if ($this->do_debug >= 2) {
			$this->edebug('SERVER -> CLIENT: ' . $reply);
		}

		if (!in_array($code, (array)$expect)) {
			$this->last_reply = null;
			$this->error = array(
				'error' => "$command command failed",
				'smtp_code' => $code,
				'detail' => substr($reply, 4)
			);
			if ($this->do_debug >= 1) {
				$this->edebug(
					'SMTP ERROR: ' . $this->error['error'] . ': ' . $reply
				);
			}
			return false;
		}

		$this->last_reply = $reply;
		$this->error = array();
		return true;
	}

	/**
	 * Send an SMTP SAML command.
	 * Starts a mail transaction from the email address specified in $from.
	 * Returns true if successful or false otherwise. If True
	 * the mail transaction is started and then one or more recipient
	 * commands may be called followed by a data command. This command
	 * will send the message to the users terminal if they are logged
	 * in and send them an email.
	 * Implements rfc 821: SAML <SP> FROM:<reverse-path> <CRLF>
	 * @param string $from The address the message is from
	 * @access public
	 * @return boolean
	 */
	public function sendAndMail($from)
	{
		return $this->sendCommand('SAML', "SAML FROM:$from", 250);
	}

	/**
	 * Send an SMTP VRFY command.
	 * @param string $name The name to verify
	 * @access public
	 * @return boolean
	 */
	public function verify($name)
	{
		return $this->sendCommand('VRFY', "VRFY $name", array(250, 251));
	}

	/**
	 * Send an SMTP NOOP command.
	 * Used to keep keep-alives alive, doesn't actually do anything
	 * @access public
	 * @return boolean
	 */
	public function noop()
	{
		return $this->sendCommand('NOOP', 'NOOP', 250);
	}

	/**
	 * Send an SMTP TURN command.
	 * This is an optional command for SMTP that this class does not support.
	 * This method is here to make the RFC821 Definition complete for this class
	 * and _may_ be implemented in future
	 * Implements from rfc 821: TURN <CRLF>
	 * @access public
	 * @return boolean
	 */
	public function turn()
	{
		$this->error = array(
			'error' => 'The SMTP TURN command is not implemented'
		);
		if ($this->do_debug >= 1) {
			$this->edebug('SMTP NOTICE: ' . $this->error['error']);
		}
		return false;
	}

	/**
	 * Send raw data to the server.
	 * @param string $data The data to send
	 * @access public
	 * @return integer|boolean The number of bytes sent to the server or false on error
	 */
	public function client_send($data)
	{
		if ($this->do_debug >= 1) {
			$this->edebug("CLIENT -> SERVER: $data");
		}
		return fwrite($this->smtp_conn, $data);
	}

	/**
	 * Get the latest error.
	 * @access public
	 * @return array
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Get the last reply from the server.
	 * @access public
	 * @return string
	 */
	public function getLastReply()
	{
		return $this->last_reply;
	}

	/**
	 * Read the SMTP server's response.
	 * Either before eof or socket timeout occurs on the operation.
	 * With SMTP we can tell if we have more lines to read if the
	 * 4th character is '-' symbol. If it is a space then we don't
	 * need to read anything else.
	 * @access protected
	 * @return string
	 */
	protected function get_lines()
	{
		// If the connection is bad, give up straight away
		if (!is_resource($this->smtp_conn)) {
			return '';
		}
		$data = '';
		$endtime = 0;
		stream_set_timeout($this->smtp_conn, $this->Timeout);
		if ($this->Timelimit > 0) {
			$endtime = time() + $this->Timelimit;
		}
		while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
			$str = @fgets($this->smtp_conn, 515);
			if ($this->do_debug >= 4) {
				$this->edebug("SMTP -> get_lines(): \$data was \"$data\"");
				$this->edebug("SMTP -> get_lines(): \$str is \"$str\"");
			}
			$data .= $str;
			if ($this->do_debug >= 4) {
				$this->edebug("SMTP -> get_lines(): \$data is \"$data\"");
			}

			//LPC: If an empty line is returned, just ignore it an read the next line
			// Hetzner server sometimes return empty response after DATA END command which then gets interpreted as an error.
			// The next response after that was a 250 but that came too late for rawSmtpMailer.
			// So we just skip empty responses to prevent this.
			// As far as I understand SMTP RFC empty responses should never occur, so we should be save
			if(trim($str) == '') continue;

			// If 4th character is a space, we are done reading, break the loop, micro-optimisation over strlen
			if ((isset($str[3]) and $str[3] == ' ')) {
				break;
			}
			// Timed-out? Log and break
			$info = stream_get_meta_data($this->smtp_conn);
			if ($info['timed_out']) {
				if ($this->do_debug >= 4) {
					$this->edebug(
						'SMTP -> get_lines(): timed-out (' . $this->Timeout . ' sec)'
					);
				}
				break;
			}
			// Now check if reads took too long
			if ($endtime and time() > $endtime) {
				if ($this->do_debug >= 4) {
					$this->edebug(
						'SMTP -> get_lines(): timelimit reached ('.
						$this->Timelimit . ' sec)'
					);
				}
				break;
			}
		}
		return $data;
	}

	/**
	 * Enable or disable VERP address generation.
	 * @param boolean $enabled
	 */
	public function setVerp($enabled = false)
	{
		$this->do_verp = $enabled;
	}

	/**
	 * Get VERP address generation mode.
	 * @return boolean
	 */
	public function getVerp()
	{
		return $this->do_verp;
	}

	/**
	 * Set debug output method.
	 * @param string $method The function/method to use for debugging output.
	 */
	public function setDebugOutput($method = 'echo')
	{
		$this->Debugoutput = $method;
	}

	/**
	 * Get debug output method.
	 * @return string
	 */
	public function getDebugOutput()
	{
		return $this->Debugoutput;
	}

	/**
	 * Set debug output level.
	 * @param integer $level
	 */
	public function setDebugLevel($level = 0)
	{
		$this->do_debug = $level;
	}

	/**
	 * Get debug output level.
	 * @return integer
	 */
	public function getDebugLevel()
	{
		return $this->do_debug;
	}

	/**
	 * Set SMTP timeout.
	 * @param integer $timeout
	 */
	public function setTimeout($timeout = 0)
	{
		$this->Timeout = $timeout;
	}

	/**
	 * Get SMTP timeout.
	 * @return integer
	 */
	public function getTimeout()
	{
		return $this->Timeout;
	}
}
