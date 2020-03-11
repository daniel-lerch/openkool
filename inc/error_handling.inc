<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2020 Renzo Lauper (renzo@churchtool.org)
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

class ExceptionHandler
{
	public function handleException(\Throwable $e) {
		global $ko_menu_akt;

		if((!defined("WARRANTY_EMAIL") || WARRANTY_EMAIL == "") || $ko_menu_akt == "install") {
			$this->printException($e);
		} else {
			$this->printError();
			$this->sendWarrantyMail($e);
		}
	}

	public function handleError($severity, $message, $file, $line) {
		if($severity == E_ERROR || $severity == E_USER_ERROR) {
			throw new ErrorException($message,0,$severity,$file,$line);
		}
	}

	protected function sendWarrantyMail(\Throwable $e) {
		global $mysql_db;

		$mailtxt  = "kOOL Error Report: " . strftime($GLOBALS["DATETIME"]["DdMY"]."  -  %T") . "\n\n";
		if($e instanceof ErrorException) {
			$mailtxt .= "Error-Nr.: " . $e->getSeverity() . "\n\n";
		}
		$mailtxt .= "Error-Str.: " . $e->getMessage() . "\n\n";
		$mailtxt .= "Error-File: " . $e->getFile() . "\n\n";
		$mailtxt .= "Error-Line: " . $e->getLine() . "\n\n";

		$mailtxt .= "User-ID: " . $_SESSION["ses_userid"] . "\n";
		$mailtxt .= "DB-Name: " . $mysql_db . "\n";
		$mailtxt .= "IP: ".ko_get_user_ip()."\n\n";

		$mailtxt .= "\n\n_POST:\n";
		$mailtxt .= var_export($_POST, TRUE);
		$mailtxt .= "\n\n_GET:\n";
		$mailtxt .= var_export($_GET, TRUE);
		$mailtxt .= "\n\nBACKTRACE:\n";
		foreach($e->getTrace() as $k => $v) {
			if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require") {
				$mailtxt .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]\n";
			} else {
				$mailtxt .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]\n";
			}
		}
		$mailtxt .= "\n\n_SESSION:\n";
		$mailtxt .= var_export($_SESSION, TRUE);
		$mailtxt .= "\n\n_COOKIE:\n";
		$mailtxt .= var_export($_COOKIE, TRUE);
		$mailtxt .= "\n\n_SERVER:\n";
		$mailtxt .= var_export($_SERVER, TRUE);

		ko_send_mail(WARRANTY_EMAIL, WARRANTY_EMAIL, '[kOOL Error]', $mailtxt);
	}

	protected function printError() {
		global $ko_path,$FILE_LOGO_BIG;
		print '<table width="50%" align="center" class="error">';
		print '<tr><td><img src="'.$ko_path.$FILE_LOGO_BIG.'" width="200" /><br /><h2>'.getLL("error_title").'</h2></td>';
		print '<td>';
		print "<h3>".getLL("error_header")."</h3>";
		print getLL("error_msg_1").'<br />';
		print sprintf(getLL("error_msg_2"), WARRANTY_EMAIL).'<br /><br />';;
		print getLL("error_msg_3").'<br />';
		print '</td></tr></table>';
	}

	protected function printException(\Throwable $e) {
		$trace = $e->getTrace();
		echo '<html><head><style type="text/css">';
		echo 'body {font-family: sans-serif;}';
		echo '.step { margin-bottom:-1px; border:1px solid silver; overflow:hidden; }';
		echo '.step .source { overflow:hidden; box-sizing:border-box; max-height:0px; transition: max-height 0.3s ease; font-family:monospace; box-shadow:inset 0px 5px 10px -10px #000;}';
		echo '.step .source > div { margin:-5px 0px; padding:0px 10px; position:relative; }';
		echo '.step.open .source { max-height:400px; }';
		echo '.step .header {border-bottom:1px solid silver; margin-bottom:-1px; background-color:#eee; cursor:pointer; line-height:19px;}';
		echo '.step .number, .step .file {float:left; padding:10px;}';
		echo '.step .number {width:24px; text-align:right;}';
		echo '.step .function {float:right; padding:10px; font-family:monospace;}';
		echo '</style></head><body>';
		echo '<h3>'.get_class($e).'</h3>';
		if($e->getCode()) {
			'<p><b>Code</b> '.$e->getCode().'</p>';
		}
		echo '<p><b>Message</b><br/>'.htmlentities($e->getMessage()).'</p>';
		$estr = (string)$e;
		if($p = strpos($estr,"\nStack trace:")) {
			$estr = substr($estr,0,$p);
		}
		echo nl2br(htmlentities($estr));
		echo '<p><b>Stack Trace</b></p>';
		array_unshift($trace,['file' => $e->getFile(),'line' => $e->getLine()]);
		foreach($trace as $n => $step) {
			echo '<div class="step">';
			if(isset($trace[$n+1])) {
				$class = $trace[$n+1]['class'];
				$function = $trace[$n+1]['function'];
				$args = $trace[$n+1]['args'];
			} else {
				$class = $function = '';
				$args = [];
			}
			$this->printTraceStep($step['file'],$step['line'],$class,$function,$args,$n+1);
			echo '</div>';
		}
		echo '</body></html>';
	}

	private function printTraceStep($file,$line,$class,$function,$args,$step) {
		global $DEBUG_EDITOR;
		echo '<div class="header" onclick="console.log(event.target); event.stopPropagation(); this.parentElement.classList.toggle(\'open\');">';
		echo '<div class="number"><b>'.$step.'</b></div>';
		if($DEBUG_EDITOR) {
			$editorLink = strtr($DEBUG_EDITOR,[
				'%FILE%' => $file,
				'%LINE%' => $line,
			]);
			echo '<div class="file"><b><a href="'.$editorLink.'" onclick="event.stopPropagation();">'.$file.':'.$line.'</a></b></div>';
		} else {
			echo '<div class="file"><b>'.$file.':'.$line.'</b></div>';
		}
		if($function) {
			echo '<div class="function">';
			if($class) {
				echo $class.'::';
			}
			echo $function.'(';
			$first = true;
			foreach($args as $arg) {
				if($first) {
					$first = false;
				} else {
					echo ',';
				}
				echo '<a title="';
				$this->printPlain($arg);
				echo '">';
				if(is_object($arg)) {
					echo get_class($arg);
				} else if(is_array($arg)) {
					echo 'Array';
				} else if(is_string($arg)) {
					if(strlen($arg) > 50) {
						$arg = substr($arg,0,47).'...';
					}
					echo "'".htmlentities($arg)."'";
				} else {
					var_export($arg);
				}
				echo '</a>';
			}
			echo ')</div>';
		}
		echo '<div style="clear:both;"></div></div>';
		echo '<div class="source"><div>';
		$source = explode('<br />',highlight_file($file,true));
		$startline = max(-1,$line-11);
		$endline = min(count($source)+1,$startline+21);//min(count($source),$line+10);
		for($l = $startline; $l < $endline; $l++) {
			if($l < 0 || $l >= count($source)) {
				echo '<br/>';
			} else {
				$n = str_repeat('&nbsp;',strlen($endline)-strlen($l+1)).($l+1);
				if($l+1 == $line) {
					echo '<div style="position:absolute; left:0px; z-index:-1; height:1.25em; width:100%; background-color:#ffffdd"></div>';
				}
				echo '<span style="color:#000000;">'.$n.'</span> <span>'.$source[$l].'</span><br/>'.PHP_EOL;
			}
		}
		echo '</div></div>';
	}

	protected function printPlain($value) {
		ob_start();
		var_dump($value);
		list($bla1,$bla2,$t) = explode(':',strip_tags(ob_get_clean()),3);
		echo ltrim($t);
	}

	public function register() {
		set_exception_handler([$this,'handleException']);
		set_error_handler([$this,'handleError']);
	}
}

if(php_sapi_name() != 'cli') {
	$exceptionHandler = new ExceptionHandler;
	$exceptionHandler->register();
}
