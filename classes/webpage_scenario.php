<?php

/**
 * Handles processing of the web query.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class webpage_scenario extends webpage {

  public function buildHTML() {
    $tid = (array_key_exists('tid', $_GET) ? (int) $_GET['tid'] : null);
    if ($tid) {
      $scen = new scenario($tid);
    } else {
      // LIST OF SCENARIOS
      return "Scenario list here"; // TODO
    }
    if ($scen->exists) {
      $this->setTitle($scen->title);

      // Tags
      $tag = "";
      foreach ($scen->tags as $t) {
        $tag .= '<a href="./?p=tags&t=' . htmlspecialchars($t) . '"><span class="label label-default">' . $t . '</span></a>' . PHP_EOL;
      }

      // BGASP bar
      $bgaspbar = $this->buildBGBar($scen->bgasp);

      // Authors
      $auths = $this->authToString($scen->author);

      // Composite score stuff, stolen from scenario::bgaspBB
      $val = 0;
      $pct = array();
      $tot = array_sum($scen->bgasp);

      $pts = array(
        B => 5,
        G => 4,
        A => 3,
        S => 2,
        P => 1,
      );
      foreach ($scen->bgasp as $cat => $num) {
        $pct[$cat] = $num / $tot;
        $val += $num * $pts[$cat];
      }
      $csr = number_format($val / $tot, 1);


      $r = <<< EOT
        	<div class="row">
		<div class="col-md-12 column">
			<h3>$scen->title <small>by $auths</small></h3>
		</div>
	</div>
        	<div class="row">
		<div class="col-md-8 column">
      <p>
        $tag
      </p>
      $bgaspbar
			<ul>
				<li>Windows Download: <a href="{$scen->dlWin}">{$scen->dlWin}</a></li>
				<li>Mac Download: <a href="{$scen->dlMac}">{$scen->dlMac}</a></li>
			</ul>
		</div>
		<div class="col-md-4 column">
			<table class="table table-condensed">
				<tbody>
					<tr><td><strong>CSR Composite</strong></td><td><strong>$csr</strong><small>/5.0</small></td></tr>
						<tr><td>Valid Reviews</td><td>$tot</td></tr>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
EOT;
      return $r;
    } else {
      $this->setTitle("Scenario not found");
      return "<p>You must specify a scenario to be selected.</p>";
    }
  }

}
