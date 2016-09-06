<?php

class Debugger {

	const FILE_DIRECTORY = "/tmp";
	const FILE_PREFIX = 'debugger';
	const FILE_EXTENSION = 'log';

	const LOG_TYPE_JSON = 'JSON';
	const LOG_TYPE_DEBUG = 'DEBUG';
	const LOG_TYPE_INFO = 'INFO';
	const LOG_TYPE_ERROR = 'ERROR';
	const LOG_TYPE_WARN = 'WARNING';

	public $_logFilePath;
	private $_logWritePath;
	public $_fileSegmentation;
	private $_counter;
	private $_serial;
  private $_pretty;
	private $_overwrite;

	function __construct($logFilePath = false, $fileSegmentation = 'NONE', $overwrite = false) {
		$this->_counter = 0;
		$this->_serial = -1;
		$this->_pretty = true;
    $this->_overwrite = ($overwrite === true);
		$this->_fileSegmentation = $fileSegmentation;
		$this->_logFilePath = $logFilePath;
		$this->generateLogWritePath();
	}

	private function generateSuffix() {
		$seg = strtoupper($this->_fileSegmentation);
		$separator = "_";
		$suffix = null;
		if($seg === 'YEAR')
			$suffix = $separator . date('Y');
		else if($seg === 'MONTH')
			$suffix = $separator . date('Y_F');
		else if($seg === 'DAY')
			$suffix = $separator . date('Y-m-d');
		else if($seg === 'HOUR')
			$suffix = $separator . date('Y-m-d_H');
		else if($seg === 'MINUTE')
			$suffix = $separator . date('Y-m-d_Hi');
		else if($seg === 'SECOND')
			$suffix = $separator . date('Y-m-d_His');
		else
			$suffix = null;

		return $suffix;
	}

	private function generateLogWritePath() {

		$fileDir = ( strncasecmp(PHP_OS, 'WIN', 3) == 0 ) ? __DIR__ : self::FILE_DIRECTORY;
		$filePrefix = self::FILE_PREFIX;
		$fileSuffix = $this->generateSuffix();
		$fileExtension = "." . self::FILE_EXTENSION;

		if( ! $this->_logFilePath or empty($this->_logFilePath) ) {
			$this->_logWritePath = $fileDir . DIRECTORY_SEPARATOR . $filePrefix . $fileSuffix . $fileExtension;
			return true;
		} else {
			try {
				$pathParts = pathinfo($this->_logFilePath);
				// In case of file path is a directory path. we will use that dir to save log.
				$fileDir = ( is_dir($this->_logFilePath) ) ? $this->_logFilePath : $pathParts['dirname'];
				$fileDir = rtrim($fileDir, "/");
				// In case of file path is a directory or file path does not contain file name, we will use preset prefix in file name
				$filePrefix = ( is_dir($this->_logFilePath) or empty( trim($pathParts['filename']) ) ) ? self::FILE_PREFIX : $pathParts['filename'];
				$fileExtension = ( empty($pathParts['extension']) ) ? "." . self::FILE_EXTENSION : "." . trim($pathParts['extension']);
				$dirSeparator = ( $fileDir === DIRECTORY_SEPARATOR) ? "" : DIRECTORY_SEPARATOR;
				if( ! is_dir($this->_logFilePath) and  ! empty(trim($pathParts['filename'])) and empty(trim($pathParts['extension'])) ) {
					$fileExtension = "";
				}
				$this->_logWritePath = trim($fileDir) . $dirSeparator . trim($filePrefix) . $fileSuffix . $fileExtension;
				return true;
			} catch (Exception $e) {
				print $e->getMessage();
			}
		}
		return false;
	}

	function setPath($logFilePath) {
		$this->_logFilePath = $logFilePath;
		$this->generateLogWritePath();
	}

	function getPath() {
		return $this->_logFilePath;
	}

	function setSeg($fileSegmentation) {
		$this->_fileSegmentation = $fileSegmentation;
		$this->generateLogWritePath();
	}

	function getSeg() {
		return $this->_fileSegmentation;
	}

	function pretty() {
		$this->_pretty = true;
	}

	function ugly() {
		$this->_pretty = false;
	}
  
  function overwrite($overwrite) {
    $this->_overwrite = ($overwrite === true);
  }

	function time($return = false) {
		$date = date('[Y-m-d H:i:s]');
		if($return)
			return $date;
		else
			print $date . PHP_EOL;
	}

	function count($action = 'INC') {
		$act = strtoupper($action);
		if($act === 'INC')
			return ++$this->_counter;
		else if($act === 'DEC')
			return --$this->_counter;
		else if($act === 'GET')
			return $this->_counter;
		else if($act === 'RESET') {
			$this->_counter = 0;
			return $this->_counter;
		}
		return false;
	}

	private function write($string) {
		try {
			if( ! file_exists($this->_logWritePath) ) {
				$touchy = @touch($this->_logWritePath);
				if( ! $touchy ) {
					print "ERROR! Could not create file: {$this->_logWritePath}";
					return false;
				}
			}

			if( ! is_writable($this->_logWritePath) ) {
				print "ERROR! Could not write to file: {$this->_logWritePath}";
				return false;
			}

      $overwriteFlag = $this->_overwrite ? 0 : FILE_APPEND;
			return file_put_contents( $this->_logWritePath, $string, $overwriteFlag );

		} catch (Exception $e) {
			print $e->getMessage();
		}
		return false;
	}

	private function preamble($logType = self::LOG_TYPE_DEBUG, $dataType) {
		$serial = ++$this->_serial;
		$time = $this->time(true);
		$newline = ( strncasecmp(PHP_OS, 'WIN', 3) == 0 ) ? "\n" : "\r\n";
		$preamble = $newline . "[$serial] $time [$logType] [DATATYPE:$dataType] ";
		return $preamble;
	}

	private function process($logType, $data, $return, $raw = false) {
		$preamble = $this->preamble($logType, gettype($data));

		if($logType == self::LOG_TYPE_DEBUG) {
			$content = var_export($data, true);
		} else if($logType == self::LOG_TYPE_JSON) {
			$content = json_encode($data, true);
		} else {
			$content = print_r($data, true);
		}

		$output = ( $raw ) ? $content : $preamble . $content;

		if($return) {
			$preTagOpening = ( $logType == self::LOG_TYPE_ERROR ) ? '<pre style="color: red;">' : '<pre>';
			$preTagClosing = '</pre>';
			$printableText = ( $this->_pretty and ! $raw ) ? $preTagOpening . $output . $preTagClosing : $output;
			return $printableText . PHP_EOL;
		} else
			return $this->write($output);

		return false;
	}

	function json($data, $return = false, $raw = false) {
		return $this->process(self::LOG_TYPE_JSON, $data, $return, $raw);
	}

	function j($data, $return = false, $raw = false) {
		return $this->json($data, $return, $raw);
	}

	function debug($data, $return = false) {
		return $this->process(self::LOG_TYPE_DEBUG, $data, $return);
	}

	function d($data, $return = false) {
		return $this->debug($data, $return);
	}

	function info($data, $return = false) {
		return $this->process(self::LOG_TYPE_INFO, $data, $return);
	}

	function i($data, $return = false) {
		return $this->info($data, $return);
	}

	function error($data, $return = false) {
		return $this->process(self::LOG_TYPE_ERROR, $data, $return);
	}

	function e($data, $return = false) {
		return $this->error($data, $return);
	}

	function warn($data, $return = false) {
		return $this->process(self::LOG_TYPE_WARN, $data, $return);
	}

	function w($data, $return = false) {
		return $this->warn($data, $return);
	}

	function msg($message = 'Hello, World!') {
		print $message . PHP_EOL;
	}

} // end of class Debugger
