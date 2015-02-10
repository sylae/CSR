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
      $this->title = "BoA CSR";
    }
  }

  protected function authToString($array) {
    $s = array();
    foreach ($array as $aid => $name) {
      $s[] = '<a href="./?auth=' . $aid . '">' . $name . '</a>';
    }
    return implode(", ", $s);
  }

  protected function buildBGBar($bgasp) {
    $pct = array();
    $pf = array();
    $tot = array_sum($bgasp);
    foreach ($bgasp as $cat => $num) {
      $pct[$cat] = ($tot != 0) ? $num / $tot * 100 : 0; // DO NOT ROUND OR THIS WILL GET FUCKED
      $pf[$cat] = number_format($pct[$cat], 2); // use this for labels.
    }
    if ($tot < 5) {
      $p = $tot * 20;
      $bgaspbar = '<div class="progress" style="min-width:8em;position:relative">'
        . '<span style="position:absolute;right:0;left:0;text-align:center;">Unranked ('.$tot.'/5)</span>'
        . '<div class="progress-bar progress-bar-striped" style="width:'.$p.'%;background-color:#aaa;color:#333;"></div>'
        . '<div class="progress-bar" style="width:'.(100-$p).'%;background-color:#aaa;color:#333;"></div>'
        . '</div>';
    } else {
      $bgaspbar = <<<EOT
<div class="progress" style="min-width:8em;">
<div class="progress-bar" style="width:{$pct[P]}%;background-color:#c00000;color:#333" data-toggle="tooltip" data-placement="bottom" title="Poor: {$pf[P]}%">{$bgasp[P]}</div>
<div class="progress-bar" style="width:{$pct[S]}%;background-color:#ed7d31;color:#333" data-toggle="tooltip" data-placement="bottom" title="Substandard: {$pf[S]}%">{$bgasp[S]}</div>
<div class="progress-bar" style="width:{$pct[A]}%;background-color:#ffc000;color:#333" data-toggle="tooltip" data-placement="bottom" title="Average: {$pf[A]}%">{$bgasp[A]}</div>
<div class="progress-bar" style="width:{$pct[G]}%;background-color:#92d050;color:#333" data-toggle="tooltip" data-placement="bottom" title="Good: {$pf[G]}%">{$bgasp[G]}</div>
<div class="progress-bar" style="width:{$pct[B]}%;background-color:#00b0f0;color:#333" data-toggle="tooltip" data-placement="bottom" title="Best: {$pf[B]}%">{$bgasp[B]}</div>
</div>
EOT;
    }
    return $bgaspbar;
  }

}
