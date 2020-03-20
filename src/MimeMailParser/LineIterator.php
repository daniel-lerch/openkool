<?php
namespace kOOL\MimeMailParser;

class LineIterator implements \Iterator
{
	protected $stream;
	protected $currentLine;
	protected $currentLineNumber;

	public function __construct($data) {
		if(is_resource($data)) {
			$this->stream = $data;
		} else {
			$this->stream = fopen('php://memory','r+');
			fwrite($this->stream,$data);
		}
		$this->rewind();
	}

	public function __destruct() {
		fclose($this->stream);
	}

	public function current() {
		return $this->currentLine;
	}

	public function key() {
		return $this->currentLineNumber;
	}

	public function next() {
		$this->currentLine = rtrim(fgets($this->stream),"\r\n");
		$this->currentLineNumber++;
	}

	public function rewind() {
		rewind($this->stream);
		$this->currentLineNumber = 0;
		$this->next();
	}

	public function valid() {
		return !feof($this->stream);
	}

	public function skipBlankLines() {
		do {
			$this->next();
		} while($this->valid() && $this->current() == '');
	}
}
