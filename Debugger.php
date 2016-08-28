<?php

class Debugger {
  
  public $_logFile = "/home/sohel/Downloads/abc.txt";
  private $_counter;
  private $_serial;
  private $_pretty;
  
  function __construct($logFilePath = false) {
    $this->_counter = 0;
    $this->_serial = -1;
    $this->_pretty = true;
  }
  
  function setPath($logFilePath) {
    $this->_logFile = $logFilePath;
  }
  
  function getPath() {
    return $this->_logFile;
  }
  
  function pretty() {
    $this->_pretty = true;
  }
  
  function ugly() {
    $this->_pretty = false;
  }

  function time($return = false) {
    $date = date('[Y-m-d H:i:s]');
    if($return)
      return $date;
    else
      print $date . PHP_EOL;
  }

  function count($action = 'INC') {
    if(strtoupper($action) == 'INC')
      return ++$this->_counter;
    else if(strtoupper($action) === 'DEC')
      return --$this->_counter;
    else if(strtoupper($action) === 'GET')
      return $this->_counter;
    else if(strtoupper($action) === 'RESET') {
      $this->_counter = 0;
      return $this->_counter;
    }
    return false;
  }
  
  private function preamble($logType = 'INFO') {
    $serial = ++$this->_serial;
    $time = $this->time(true);
    $preamble = "[$serial] $time [$logType] ";
    return $preamble;
  }

  function write($string) {
    if( ! file_exists($this->_logFile) ) {
      try {
        @touch($this->_logFile);
      } catch (Exception $e) {
        print $e->getMessage();
        return;
      }
    }

    try {
      if( ! is_writable($this->_logFile) ) {   
        print "ERROR! Log file is not writable!";
      }
    } catch (Exception $e) {
      
    }
  }
  
  private function format($logtype, $data) {
    $dataType = gettype($data);
    $text = $this->preamble($logtype) . "[DATATYPE:$dataType] " . print_r($data, true) . PHP_EOL;
    return $text;
  }

  function debug($data, $return = false) {
    $this->format('DEBUG', $data);
    if($return) {
      $finalText = ($this->_pretty) ? "<pre>" . $text . "</pre>" : $text;
      return $finalText;
    } else {
      //
    }
  }

  function info() {
    
  }
  
  function error() {
    
  }

  function warn() {
    
  }

  function msg($message = 'Hello, World!') {
    print $message . PHP_EOL;
  }

  
}
