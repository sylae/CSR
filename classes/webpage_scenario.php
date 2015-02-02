<?php

/**
 * Handles processing of the web query.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class webpage_scenario extends webpage {

  public function buildHTML() {
    $tid = (array_key_exists('tid', $_GET) ? (int)$_GET['tid'] : null);
    if ($tid) {
      $scen = new scenario($tid);
      $this->setTitle($scen->title);
      $r = "<h3>".$scen->title." <small>by ".$this->authToString($scen->author)."</small></h3>".PHP_EOL;
      return $r;
    } else {
      $this->setTitle("Scenario not found");
      return "<p>You must specify a scenario to be selected.</p>";
    }
  }

}
