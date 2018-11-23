<?php

namespace HelloLog;

class HelloLog {

  const FILE_DIRECTORY = "/tmp";
  const FILE_PREFIX    = 'hellolog';
  const FILE_EXTENSION = 'log';

  const LOG_TYPE_JSON  = 'JSON';
  const LOG_TYPE_DEBUG = 'DEBUG';
  const LOG_TYPE_INFO  = 'INFO';
  const LOG_TYPE_ERROR = 'ERROR';
  const LOG_TYPE_WARN  = 'WARNING';

  public $_fileSegmentation;
  public $_logFilePath;

  private $_logWritePath;
  private $_counter;
  private $_serial;
  private $_pretty;
  private $_overwrite;

  /**
   * Constructor
   *
   * @param string $logFilePath Path of the log file.
   * @param string $fileSegmentation Segmentation to be used for the log file. @see generateSuffix() function.
   * @param bool $overwrite Whether to overwrite the log file or not.
   */
  public function __construct($logFilePath = false, $fileSegmentation = 'NONE', $overwrite = false) {
    $this->_counter          = 0;
    $this->_serial           = -1;
    $this->_pretty           = true;
    $this->_overwrite        = ($overwrite === true);
    $this->_fileSegmentation = $fileSegmentation;
    $this->_logFilePath      = $logFilePath;
    $this->generateLogWritePath();
  }

  /**
   * Generates the log file name suffix.
   *
   * @return string Suffix.
   */
  private function generateSuffix() {
    $seg       = strtoupper($this->_fileSegmentation);
    $separator = "_";
    $suffix    = null;
    if ($seg === 'YEAR') {
      $suffix = $separator . date('Y');
    } else if ($seg === 'MONTH') {
      $suffix = $separator . date('Y_F');
    } else if ($seg === 'DAY') {
      $suffix = $separator . date('Y-m-d');
    } else if ($seg === 'HOUR') {
      $suffix = $separator . date('Y-m-d_H');
    } else if ($seg === 'MINUTE') {
      $suffix = $separator . date('Y-m-d_Hi');
    } else if ($seg === 'SECOND') {
      $suffix = $separator . date('Y-m-d_His');
    } else {
      $suffix = null;
    }

    return $suffix;
  }

  /**
   * Generates the log file path.
   *
   * @return bool
   */
  private function generateLogWritePath() {

    $fileDir       = (strncasecmp(PHP_OS, 'WIN', 3) == 0) ? __DIR__ : self::FILE_DIRECTORY;
    $filePrefix    = self::FILE_PREFIX;
    $fileSuffix    = $this->generateSuffix();
    $fileExtension = "." . self::FILE_EXTENSION;

    if (!$this->_logFilePath or empty($this->_logFilePath)) {
      $this->_logWritePath = $fileDir . DIRECTORY_SEPARATOR . $filePrefix . $fileSuffix . $fileExtension;
      return true;
    } else {
      try {
        $pathParts = pathinfo($this->_logFilePath);
        // In case of file path is a directory path. we will use that dir to save log.
        $fileDir = (is_dir($this->_logFilePath)) ? $this->_logFilePath : $pathParts['dirname'];
        $fileDir = rtrim($fileDir, "/");
        // In case of file path is a directory or file path does not contain file name, we will use preset prefix in file name
        $filePrefix    = (is_dir($this->_logFilePath) or empty(trim($pathParts['filename']))) ? self::FILE_PREFIX : $pathParts['filename'];
        $fileExtension = (empty($pathParts['extension'])) ? "." . self::FILE_EXTENSION : "." . trim($pathParts['extension']);
        $dirSeparator  = ($fileDir === DIRECTORY_SEPARATOR) ? "" : DIRECTORY_SEPARATOR;
        if (!is_dir($this->_logFilePath) and !empty(trim($pathParts['filename'])) and empty(trim($pathParts['extension']))) {
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

  public function setPath($logFilePath) {
    $this->_logFilePath = $logFilePath;
    $this->generateLogWritePath();
  }

  public function getPath() {
    return $this->_logFilePath;
  }

  public function setSeg($fileSegmentation) {
    $this->_fileSegmentation = $fileSegmentation;
    $this->generateLogWritePath();
  }

  public function getSeg() {
    return $this->_fileSegmentation;
  }

  /**
   * Pretty log.
   */
  public function pretty() {
    $this->_pretty = true;
  }

  /**
   * Log as it is.
   */
  public function ugly() {
    $this->_pretty = false;
  }

  /**
   * Overwrite existing log file.
   *
   * @param bool $overwrite true, overwrites log file each time; appends to the log file otherwise.
   */
  public function overwrite($overwrite) {
    $this->_overwrite = ($overwrite === true);
  }

  /**
   * Standard timestamp
   */
  public function time($return = false) {
    $date = date('[Y-m-d H:i:s]');
    if ($return) {
      return $date;
    } else {
      print $date . PHP_EOL;
    }

  }

  /**
   * Built in counter
   *
   * @param string $action Counter actions. INCrement, DECrement, GET or RESET counter value.
   * @return int Counter value.
   */
  public function count($action = 'INC') {
    $act = strtoupper($action);
    if ($act === 'INC') {
      return ++$this->_counter;
    } else if ($act === 'DEC') {
      return --$this->_counter;
    } else if ($act === 'GET') {
      return $this->_counter;
    } else if ($act === 'RESET') {
      $this->_counter = 0;
      return $this->_counter;
    }
    return false;
  }

  /**
   * Writes the log in log file.
   *
   * @param string $string Log string file to be written.
   * @return int|bool Bytes written or false in case of failure.
   */
  private function write($string) {
    try {
      if (!file_exists($this->_logWritePath)) {
        $touchy = @touch($this->_logWritePath);
        if (!$touchy) {
          print "ERROR! Could not create file: {$this->_logWritePath}";
          return false;
        }
      }

      if (!is_writable($this->_logWritePath)) {
        print "ERROR! Could not write to file: {$this->_logWritePath}";
        return false;
      }

      $overwriteFlag = $this->_overwrite ? 0 : FILE_APPEND;
      return file_put_contents($this->_logWritePath, $string, $overwriteFlag);

    } catch (Exception $e) {
      print $e->getMessage();
    }
    return false;
  }

  /**
   * Returns the log preamble.
   *
   * @return string $preamble
   */
  private function preamble($logType = self::LOG_TYPE_DEBUG, $dataType) {
    $serial   = ++$this->_serial;
    $time     = $this->time(true);
    $newline  = (strncasecmp(PHP_OS, 'WIN', 3) == 0) ? "\n" : "\r\n";
    $preamble = $newline . "[$serial] $time [$logType] [DATATYPE:$dataType] ";
    return $preamble;
  }

  /**
   * Processes the log generation.
   *
   * @return int|bool Bytes written or false in case of failure.
   */
  private function process($logType, $data, $return, $raw = false) {
    $preamble = $this->preamble($logType, gettype($data));

    if ($logType == self::LOG_TYPE_DEBUG) {
      $content = var_export($data, true);
    } else if ($logType == self::LOG_TYPE_JSON) {
      $content = json_encode($data, true);
    } else {
      $content = print_r($data, true);
    }

    $output = ($raw) ? $content : $preamble . $content;

    if ($return) {
      $preTagOpening = ($logType == self::LOG_TYPE_ERROR) ? '<pre style="color: red;">' : '<pre>';
      $preTagClosing = '</pre>';
      $printableText = ($this->_pretty and !$raw) ? $preTagOpening . $output . $preTagClosing : $output;
      return $printableText . PHP_EOL;
    } else {
      return $this->write($output);
    }

    return false;
  }

  /**
   * Logs in JSON format.
   */
  public function json($data, $return = false, $raw = false) {
    return $this->process(self::LOG_TYPE_JSON, $data, $return, $raw);
  }

  /**
   * Logs in JSON format.
   *
   * @see json()
   */
  public function j($data, $return = false, $raw = false) {
    return $this->json($data, $return, $raw);
  }

  /**
   * Logs with debug tag.
   */
  public function debug($data, $return = false) {
    return $this->process(self::LOG_TYPE_DEBUG, $data, $return);
  }

  /**
   * Logs with debug tag.
   *
   * @see debug()
   */
  public function d($data, $return = false) {
    return $this->debug($data, $return);
  }

  /**
   * Logs with info tag.
   */
  public function info($data, $return = false) {
    return $this->process(self::LOG_TYPE_INFO, $data, $return);
  }

  /**
   * Logs with info tag.
   *
   * @see info()
   */
  public function i($data, $return = false) {
    return $this->info($data, $return);
  }

  /**
   * Logs with error tag.
   */
  public function error($data, $return = false) {
    return $this->process(self::LOG_TYPE_ERROR, $data, $return);
  }

  /**
   * Logs with error tag.
   *
   * @see error()
   */
  public function e($data, $return = false) {
    return $this->error($data, $return);
  }

  /**
   * Logs with warn tag.
   */
  public function warn($data, $return = false) {
    return $this->process(self::LOG_TYPE_WARN, $data, $return);
  }

  /**
   * Logs with waen tag.
   *
   * @see warn()
   */
  public function w($data, $return = false) {
    return $this->warn($data, $return);
  }

  /**
   * Prints a message.
   *
   * @param string $message Message to print.
   */
  public function msg($message = 'Hello, World!') {
    print $message . PHP_EOL;
  }

} // end of class HelloLog
