<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
*      2013 Christoph Fischer (chris@toph.de)
*  All rights reserved
*
*  This script is part of the kOOL project. The kOOL project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  kOOL is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/



//quoted_printable_encode() is part of PHP starting with v5.3. So only define if not defined yet.
if(!function_exists('quoted_printable_encode')) {
	function quoted_printable_encode($input, $line_max = 76) {
		$hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
		$lines = preg_split("/(?:\r\n|\r|\n)/", $input);
		$eol = "\r\n";
		$linebreak = "=0D=0A";
		$escape = "=";
		$output = "";

		for ($j=0;$j<count($lines);$j++) {
			$line = $lines[$j];
			$linlen = strlen($line);
			$newline = "";
			for($i = 0; $i < $linlen; $i++) {
				$c = substr($line, $i, 1);
				$dec = ord($c);
				if ( ($dec == 32) && ($i == ($linlen - 1)) ) { // convert space at eol only
					$c = "=20"; 
				} elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { // always encode "\t", which is *not* required
					$h2 = floor($dec/16); $h1 = floor($dec%16); 
					$c = $escape.$hex["$h2"].$hex["$h1"]; 
				}
				if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
					$output .= $newline.$escape.$eol; // soft line break; " =\r\n" is okay
					$newline = "    ";
				}
				$newline .= $c;
			} // end of for
			$output .= $newline;
			if ($j<count($lines)-1) $output .= $linebreak;
		}
		return trim($output);
	}//quoted_printable_encode()
}




if(isset($_GET['id']) && (int)$_GET['id'] > 0) {
	$ko_path = '../../';
	require($ko_path.'inc/ko.inc');

	$id = format_userinput($_GET['id'], 'uint');
	if(!$id) exit;

	if(!ko_module_installed('leute')) exit;
	ko_get_access('leute');
	if($access['leute']['ALL'] < 1 && $access['leute'][$id] < 1) exit;

	$v = new vCard();
	$v->addPerson($id);
	$v->outputCard();
}




class vCard {
	var $properties;
	var $filename;
	var $output;
	var $utf8;
	var $config;
	var $country_codes;


	function __construct($_utf8=TRUE) {
		global $VCARD_PROPERTIES;
		global $COUNTRY_CODES;

		$this->utf8 = $_utf8;
		$this->config = $VCARD_PROPERTIES;
		$this->country_codes = $COUNTRY_CODES;
	}
	
	// override field names through config
	// e.g. $VCARD_PROPERTIES['override']['telp']='telg';
	//      would use the telg field instead of telp.
	function _o($name) {
		return ($this->config['override'][$name] ? $this->config['override'][$name] : $name);
	}
	
	// format field according to defined formatters 
	function _f($field, $value) {
		if ($formatConf = $this->config['format'][$field]) {
			if(function_exists('ko_vcard_formatter_'.$formatConf[0])) {
				call_user_func_array('ko_vcard_formatter_'.$formatConf[0], array(&$value, $formatConf[1]));
			}
			return $value;
		} else return $value;
	}
	
	// encode field value
	function encode($prop, $value) {
		if ($e = $this->config['encoding'][$prop]) {
			switch ($e) {
				case 'QUOTED-PRINTABLE':
					$value = $this->utf8 ? utf8_encode($value) : $value;
					return $this->escape(quoted_printable_encode($value));
					break;				
			}
		} else if ($this->utf8) {
			return $this->escape(utf8_encode($value));
		} else {
			return $this->escape($value);
		}
	}
	
	function getEncoding($prop) {
		return $this->config['encoding'][$prop];
	}


	function addPerson($person, $userid='') {
		global $access;

		//Allow sabreDAV to pass userid without having to set in the session
		if(!$userid) $userid = $_SESSION['ses_userid'];

		unset($this->properties);

		//Get person if ID is given
		if(!is_array($person)) {
			$pid = (int)$person;
			if(!$pid) return FALSE;
			ko_get_person_by_id($pid, $person);
			$this->filename = format_userinput($person['vorname'].$person['nachname'], 'js').'.vcf';
		} else {
			$this->filename = 'kOOL_'.date('Ymd_His').'.vcf';
		}

		$las = ko_get_leute_admin_spalten($userid, 'all', $person['id']);

		//determine country-code,
		$default_country_code = '';
		$resident_of = $person['land'];
		$client_default_country_code = ko_get_setting('sms_country_code');
		$resident_of_country_code = '';
		$keep_zero = false;
		foreach ($this->country_codes as $country_code => $countries) {
			foreach ($countries['names'] as $country) {
				if (strtolower($resident_of) == strtolower($country)) {
					$resident_of_country_code = $country_code;
					$keep_zero = $countries['keep_zero'] === true;
					break;
				}
			}
		}
		if ($resident_of_country_code == '') {
			if ($client_default_country_code == '') {
				$resident_of_country_code = $default_country_code;
			}
			else {
				$resident_of_country_code = $client_default_country_code;
			}
		}

		//add fields 
		foreach($this->config['fields'] as $propKey => $prop) {
			$propConfig = $prop['_'];
			unset ($prop['_']);

			$separator = ($propConfig['sep'] ? $propConfig['sep'] : ';');
			$encoding = $this->getEncoding($propKey); 
			//$fullKey = ($encoding ? $propKey.';ENCODING='.$encoding : $propKey);
			if ($encoding) {
				$fullKey = $propKey.';ENCODING='.$encoding;
			}
			else if ($this->utf8) {
				$fullKey = ($propKey.';CHARSET=UTF-8');
			}
			else {
				$fullKey = ($propKey.';CHARSET=ISO-8859-1');
			}
			
			if($propConfig['text']) {
				$this->properties[$fullKey] = $propConfig['text'];
			} else {
				$tmp = array();
				foreach($prop as $fields) {
					foreach(explode('|', $fields) as $field) {
						if(!is_null($field) && $field != '') {
							//override, if necessary
							$field = $this->_o($field);

							//Check for column access
							if($las === FALSE || !is_array($las['view']) || in_array($field, $las['view'])) $ok = TRUE;
							else $ok = FALSE;

							//Add country code if necessary
							if($ok && $person[$field] != '' && $person[$field] != '0000-00-00' && $person[$field] != '0000-00-00 00:00:00') {
								$field_content = trim($person[$field]);
								if (substr($propKey, 0, 4) == 'TEL;' && $field_content != '') {
									if (substr($field_content, 0, 1) != '+' && substr($field_content, 0, 2) != '00' && $resident_of_country_code != '') {
										if (substr($field_content, 0, 1) == '0' && !$keep_zero) {
											$field_content = '+' . $resident_of_country_code . substr($field_content, 1, strlen($field_content) - 1);
										}
										else {
											$field_content = '+' . $resident_of_country_code . $field_content;
										}
									}
								}

								//format, if necessary
								$value = $this->_f($field, $field_content);
								$tmp[] = $this->encode($propKey, $value); 
							}
						} else $tmp[] = '';
					}
				}
				if (count($tmp)) $this->properties[$fullKey] = join($separator, $tmp);
			}
		}
		ksort($this->properties);
				
		$this->output .= $this->getVCard($person['id']);
	}//addPerson()



	function writeCard() {
		global $ko_path;

		$filename = $ko_path.'download/kOOL_'.date('Ymd_His').'.vcf';

		$fp = @fopen($filename, 'w');
		fputs($fp, $this->output);
		fclose($fp);

		return $filename;
	}//writeCard()


	function outputCard() {
		$filename = $this->getFileName();

		header('Cache-Control:');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-Length: '.strlen($output));
		header('Connection: close');
		header('Content-Type: text/x-vCard; name='.$filename.'');

		echo $this->output;
	}//outputCard();

	
	// UNTESTED !!!
	function setPhoto($type, $photo) { // $type = "GIF" | "JPEG"
		$this->properties["PHOTO;TYPE=$type;ENCODING=BASE64"] = base64_encode($photo);
	}
	
	function getVCard($pid) {
		$text = "BEGIN:VCARD\r\n";
		$text .= 'VERSION:'.$this->config['version']."\r\n";
		$text .= 'UID:'.md5($BASE_URL).'-'.$pid."\r\n";
		$props = $this->properties;
		unset($props['VERSION']);
		foreach($props as $key => $value) {
			$text.= "$key:".$value."\r\n";
		}
		$text.= "END:VCARD\r\n";
		return $text;
	}
	
	function getFileName() {
		return ($this->filename != '.vcf' && $this->filename != '') ? $this->filename : 'vcard.vcf';
	}


	function escape($string) {
		return str_replace(";","\;",$string);
	}//escape()


}//class vCard





function ko_vcard_formatter_phone(&$number, $param) {
}

function ko_vcard_formatter_date(&$date, $param) {
	$date = sql_datum($date);
}

function ko_vcard_formatter_tzdate(&$date, $param) {
	$date = date_convert_timezone($date, $param);
}
