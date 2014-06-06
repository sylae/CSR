<?php

/**
 * Dummy class to hold common utilities
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class CSR {

  private $config;
  private $db;
  
  function __construct() {
    global $config;
    $this->config = $config;
    $this->_setDB();
  }

  private function _setDB() {
    $this->db = & MDB2::singleton($this->config['db']);
    if (PEAR::isError($this->db)) {
      die($this->db->getMessage());
    }
    $this->db->loadModule('Extended', null, false);
  }

}
