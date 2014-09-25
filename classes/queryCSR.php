<?php

/**
 * Class for assorted CSR Querying
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class queryCSR extends CSR {
  public $res;

  public function __construct($args = null) {
    parent::__construct();
    $this->_query($args);
  }

  public function _query($args) {
    $this->res =  array();
  }

}
