<?php
namespace kOOL\MimeMailParser;

require __DIR__.'/MimePart.php';

class Message
{
	protected $part;
	protected $targetEncoding;

	public function __construct($targetEncoding = 'utf-8') {
		$this->part = new MimePart;
		$this->targetEncoding = $targetEncoding;
	}

	public function parse($message) {
		$this->part->parse($message);
	}

	public function getBody($part = null) {
		if($part === null) $part = $this->part;
		return $part->getBody($this->targetEncoding);
	}

	public function getHeader($header,$part = null) {
		if($part === null) $part = $this->part;
		return $part->getHeader($header,$this->targetEncoding);
	}

	public function getHeaders($lowerCaseKeys = false,$part = null) {
		if($part === null) $part = $this->part;
		return $part->getHeaders($this->targetEncoding,$lowerCaseKeys);
	}

	public function getHeaderParameter($header,$parameter,$part = null) {
		if($part === null) $part = $this->part;
		return $part->getHeaderParameter($header,$parameter,$this->targetEncoding);
	}

	public function getTextBody($convertHtml = false,$part = null) {
		if($part === null) $part = $this->part;
		if(strpos($part->getContentType(),'message/') === 0) {
			return $this->getTextBody($convertHtml,$part->getChildren()[0]);
		}
		if($part->isMultiPart()) {
			$html = false;
			foreach($part->getChildren() as $child) {
				$contentType = $child->getContentType();
				if($contentType == 'text/plain') {
					return $child->getBody($this->targetEncoding);
				} else if($contentType == 'text/html') {
					$html = $child;
				}
			}
			if($html) {
				return $this->html2text($html->getBody($this->targetEncoding));
			}
		} else if($part->getContentType() == 'text/plain') {
			return $part->getBody($this->targetEncoding);
		} else if($part->getContentType() == 'text/html' && $convertHtml) {
			return $this->html2text($part->getBody($this->targetEncoding));
		} else {
			return '';
		}
	}

	protected function html2text($html) {
		global $BASE_PATH;
		require_once($BASE_PATH.'inc/class.html2text.php');
		$html2text = new \html2text($html);
		return $html2text->get_text();
	}

	public function getHtmlBody($part = null) {
		if($part === null) $part = $this->part;
		if($part->isMultiPart()) {
			foreach($part->getChildren() as $child) {
				if($child->getContentType() == 'text/html') {
					return $child->getBody($this->targetEncoding);
				}
			}
		} else if($part->getContentType() == 'text/html') {
			return $part->getBody($this->targetEncoding);
		} else {
			return '';
		}
	}

	public function getAllTextBodies($convertHtml = false,$part = null) {
		if($part === null) $part = $this->part;
		$bodies = [];
		if($body = $this->getTextBody($convertHtml,$part)) {
			$bodies[] = $body;
		}
		foreach($part->getChildren() as $child) {
			if(substr($child->getContentType(),0,8) == 'message/') {
				$bodies = array_merge($bodies,$this->getAllTextBodies($convertHtml,$child));
			}
		}
		return $bodies;
	}

	public function getAllHtmlBodies($part = null) {
		if($part === null) $part = $this->part;
		$bodies = [];
		if($body = $this->getHtmlBody($part)) {
			$bodies[] = $body;
		}
		foreach($part->getChildren() as $child) {
			if(substr($child->getContentType(),0,8) == 'message/') {
				$bodies = array_merge($bodies,$this->getAllHtmlBodies($child));
			}
		}
		return $bodies;
	}

	public function getAllAttachments($part = null) {
		$attachments = array();
		if($part === null) $part = $this->part;
		list($contentDisposition,) = explode(';',$part->getHeader('content-disposition','us-ascii'),2);
		if(in_array(strtolower($contentDisposition),array('inline','attachment'))) {
			$contentType = $part->getContentType();
			if(strpos($contentType,'message/') !== 0 && strpos($contentType,'multipart/') !== 0) {
				$attachments[] = $part;
			}
		}
		foreach($part->getChildren() as $child) {
			$attachments = array_merge($attachments,$this->getAllAttachments($child));
		}
		return $attachments;
	}

	public function getFilename($part) {
		return $part->getFilename($this->targetEncoding);
	}

	public function getDate() {
		return new \DateTime($this->part->getHeader('date','us-ascii'));
	}
}

