<?php
namespace kOOL\MimeMailParser;

include __DIR__.'/LineIterator.php';

class  MimePart
{
	protected $headers;
	protected $origHeaders;
	protected $body;
	protected $children = array();

	public function getHeader($header,$targetEncoding) {
		$header = strtolower($header);
		if(isset($this->headers[$header])) {
			if(is_array($this->headers[$header])) {
				return array_map(function($value) use($targetEncoding) {
					return iconv_mime_decode($value,0,$targetEncoding);
				},$this->headers[$header]);
			} else {
				return iconv_mime_decode($this->headers[$header],0,$targetEncoding);
			}
		}
		return null;
	}

	public function getHeaders($targetEncoding,$lowerCaseKeys = false) {
		$headers = array();
		foreach($this->headers as $name => $value) {
			$headers[$lowerCaseKeys ? $name : $this->origHeaders[$name]] = $this->getHeader($name,$targetEncoding);
		}
		return $headers;
	}

	public function getHeaderParameter($header,$parameter,$targetEncoding) {
		$header = strtolower($header);
		if(isset($this->headers[$header]) && preg_match_all('/'.$parameter.'(\*([0-9]+))?(\*)?=([^\s";]+|("([^"]*)"))/i',$this->headers[$header],$matches,PREG_SET_ORDER)) {
			$parts = array_combine(array_column($matches,2),$matches);
			if(isset($parts[''])) {
				$parts = array($parts['']);
			}
			ksort($parts);
			reset($parts);
			$encoded = current($parts)[3] == '*';
			if($encoded) {
				list($encoding,$language,$value) = explode('\'',current($parts)[4]);
				$parts[key($parts)][4] = $value;
			}
			$value = '';
			foreach($parts as $part) {
				if($part[3] == '*') {
					$value .= iconv($encoding,$targetEncoding,rawurldecode($part[4]));
				} else {
					$value .= iconv_mime_decode(isset($part[6]) && $part[6] ? $part[6] : $part[4],0,$targetEncoding);
				}
			}
			return $value;
		}
		return null;
	}

	public function getContentType() {
		if(isset($this->headers['content-type'])) {
			list($contentType,) = explode(';',$this->headers['content-type']);
			return $contentType;
		} else {
			return null;
		}
	}

	public function getBody($targetEncoding = null) {
		$body = $this->body;

		if(isset($this->headers['content-transfer-encoding'])) {
			switch($this->headers['content-transfer-encoding']) {
				case 'quoted-printable':
					$body = quoted_printable_decode($body);
					break;
				case 'base64':
					$body = base64_decode($body);
					break;
			}
		}
		if(($charset = $this->getHeaderParameter('content-type','charset','us-ascii')) && $targetEncoding !== null) {
			$body = iconv($charset,$targetEncoding,$body);
		}
		return $body;
	}

	public function getChildren() {
		return $this->children;
	}

	public function isMultiPart() {
		if(isset($this->headers['content-type'])) {
			return substr($this->headers['content-type'],0,9) == 'multipart';
		} else {
			return null;
		}
	}

	public function getFilename($targetEncoding) {
		$filename = $this->getHeaderParameter('content-disposition','filename',$targetEncoding);
		if(!$filename) {
			$filename = $this->getHeaderParameter('content-type','name',$targetEncoding);
		}
		return $filename;
	}

	public function parse($message) {
		$this->parsePart(new LineIterator($message),null);
	}

	private function parsePart(LineIterator $lines,$boundary) {
		$this->parseHeaders($lines);
		if($this->isMultiPart()) {
			$this->parseChildren($lines,$boundary);
		} else if(substr($this->getContentType(),0,8) == "message/") {
			$this->parseChild($lines,$boundary);
		} else {
			$this->parseBody($lines,$boundary);
		}
	}

	private function parseHeaders(LineIterator $lines) {
		$header = null;
		while($lines->current() != '') {
			if(ctype_space(substr($lines->current(),0,1))) {
				if(is_array($this->headers[$header])) {
					$this->headers[$header][count($this->headers[$header])-1] .= PHP_EOL.$lines->current();
				} else {
					$this->headers[$header] .= PHP_EOL.$lines->current();
				}
			} else {
				list($origHeader,$line) = explode(':',$lines->current(),2);
				$header = strtolower($origHeader);
				$this->origHeaders[$header] = $origHeader;
				$line = ltrim($line);
				if(isset($this->headers[$header])) {
					if(!is_array($this->headers[$header])) {
						$this->headers[$header] = array($this->headers[$header]);
					}
					$this->headers[$header][] = $line;
				} else {
					$this->headers[$header] = $line;
				}
			}
			$lines->next();
		}
		$lines->next();
	}

	private function parseChildren(LineIterator $lines,$boundary) {
		$innerBoundary = $this->getHeaderParameter('content-type','boundary','us-ascii');
		$this->parseBody($lines,$innerBoundary);
		while($lines->valid() && strpos($lines->current(),'--'.$innerBoundary.'--') === false) {
			$lines->skipBlankLines();
			$this->parseChild($lines,$innerBoundary);
		}
		$lines->skipBlankLines();
	}

	private function parseChild(LineIterator $lines,$boundary) {
		$child = new MimePart;
		$child->parsePart($lines,$boundary);
		$this->children[] = $child;
	}

	private function parseBody(LineIterator $lines,$boundary) {
		while($lines->valid() && strpos($lines->current(),'--'.$boundary) !== 0) {
			$this->body .= $lines->current()."\n";
			$lines->next();
		}
	}
}

