<?php

/**
 * Dummy class to hold common utilities
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class CSR {

  public $config;
  public $db;

  /**
   * Map strings to BGASP consts and vice-versa
   * @var array
   */
  public $map = array(
    'Best' => B,
    'Good' => G,
    'Average' => A,
    'Substandard' => S,
    'Poor' => P,
  );

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
  
  function l($t) {
    if ($this->config['debug']) {
      echo '['.date('H:i:s').'] '.$t.PHP_EOL;
    }
  }

}
