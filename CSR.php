<?php

/**
 * Dummy class to hold common utilities
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class CSR {

  /**
   * Global configuration array
   * @var array 
   */
  protected $config;

  /**
   * Global database object
   * @var MDB2_Driver_Common 
   */
  protected $db;

  /**
   * Map strings to BGASP consts and vice-versa
   * @var array
   */
  protected $map = array(
    'Best' => B,
    'Good' => G,
    'Average' => A,
    'Substandard' => S,
    'Poor' => P,
  );

  /**
   * Constructor. Set up the configuration and database for the child class.
   * 
   * @global array $config Global configuration array from @file config.php
   */
  public function __construct() {
    global $config;
    $this->config = $config;
    $this->_setDB();
  }

  /**
   * Set up $this->db for the child class. MDB2 does most of the work, and
   * operates using a singleton--so we don't get thousands of database classes
   * floating around.
   */
  private function _setDB() {
    $this->db = & MDB2::singleton($this->config['db']);
    if (PEAR::isError($this->db)) {
      die($this->db->getMessage());
    }
    $this->db->loadModule('Extended', null, false);
  }

  /**
   * Log something to the screen (if debug is enabled)
   * @param string $t Text to be logged
   */
  public function l($t) {
    if ($this->config['debug']) {
      echo '[' . date('H:i:s') . '] ' . $t . PHP_EOL;
    }
  }

}
