<?php

/**
 * Handles processing of the web query.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class webpage extends CSR {

  public $html = "";
  public $title = "BoA CSR";

  function __construct() {
    parent::__construct();
    $this->html = $this->buildHTML();
  }

  /**
   * Generates the HTML to send to the client.
   * 
   * @return string html to be sent to the client
   */
  public function buildHTML() {
    return "";
  }

  public function setTitle($string = null) {
    if ($string) {
      $this->title = $string . " - BoA CSR";
    } else {
      $this->title =  "BoA CSR";
    }
  }
  
  protected function authToString($array) {
    $s = array();
    foreach ($array as $aid => $name) {
      $s[] = '<a href="./?auth='.$aid.'">'.$name.'</a>';
    }
    return implode(", ", $s);
  }

}
