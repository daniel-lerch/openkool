<?php
namespace LPC\LpcEsr\CashManagement;

use LPC\LpcEsr\CashManagement;

require_once('Reader.php');

class OfflineReader extends Reader
{
	protected $sourceFolder;

	public function __construct() {
		parent::__construct('', 22);
	}

	public function setSourceFolder($sourceFolder) {
		$this->sourceFolder = rtrim($sourceFolder,'/').'/';
	}

	public function readAll() {
		$this->messagesToProcess = [];
		@mkdir($this->messageFolder,0777,true);
		@mkdir($this->messageFolder.'done');
		@mkdir($this->messageFolder.'unmatched');
		if(!file_exists($this->messageFolder.'.htaccess')) {
			file_put_contents($this->messageFolder.'.htaccess','Deny from all');
		}
		$sourceDir = dir($this->sourceFolder);
		while (($file = $sourceDir->read()) !== false) {
			if(in_array(substr($file,-4),['.zip','.xml']) &&
				!file_exists($this->messageFolder.'done/'.$file)) {
				rename($this->sourceFolder.$file,$this->messageFolder.$file);
			} else if (in_array(substr($file,-4),['.zip','.xml'])) {
				rename($this->sourceFolder.$file,$this->messageFolder.'unmatched/'.$file);
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

	public function readOne($path) {
		$this->processFile($path);
		foreach($this->processors as $processor) {
			$processor->finalize();
		}
	}
}
