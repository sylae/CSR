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
      return $this->_buildScenarioList();
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

  private function _buildScenarioList() {
    $pts = array(
      B => 5,
      G => 4,
      A => 3,
      S => 2,
      P => 1,
    );
    $query = 'SELECT * FROM composite;';
    $res = & $this->db->query($query);
    $scens = array();
    while (($resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
      $bgasp = array(
        B => (int) $resu['b'],
        G => (int) $resu['g'],
        A => (int) $resu['a'],
        S => (int) $resu['s'],
        P => (int) $resu['p'],
      );
      $val = 0;
      foreach ($bgasp as $cat => $num) {
        $val += $num * $pts[$cat];
      }
      $sum = array_sum($bgasp);
      if ($sum == 0) {
        $csr = 0;
      } else {
        $csr = $val / $sum;
      }
      $scens[] = array(
        'tid' => $resu['tid'],
        'title' => $resu['title'],
        'author' => $resu['author'],
        'rating' => $csr,
        'reviews' => $sum,
        'bgasp' => $bgasp,
      );
    }

    $r = <<< EOT
        	<div class="row">
		<div class="col-md-12 column">
			<h3>Scenarios</h3>
      <table class="table table-condensed">
        <tr>
          <th>Scenario</th>
          <th colspan="2">Rating</th>
        </tr>
EOT;
    usort($scens, "webpage_scenario::_sortByScore");
    foreach ($scens as $d => $payload) {
      $bar = $this->buildBGBar($payload['bgasp']);
      if ($payload['reviews'] < 5) {
        $linetwo = "<td>Unranked, needs " . (5 - $payload['reviews']) . " more " . ((5 - $payload['reviews']) == 1 ? "review" : " reviews") . $bar . "</td>";
      } else {
        $linetwo = "<td><strong>" . number_format($payload['rating'], 1) . "</strong><small>/5.0</small> with " . $payload['reviews'] . (($payload['reviews'] == 1) ? " review" : " reviews") . $bar . "</td>";
      }
      $r.= "<tr><td><a href=\".?p=scenario&tid=" . $payload['tid'] . "\">" .
        $payload['title'] . "</a><br /><small><em>By " . $payload['author'] . "</em></small></td>" . $linetwo . "</tr>";
    }
    $r.= <<< EOT
      </table>
		</div>
	</div>
EOT;
    return $r;
  }

  private function _sortByScore($a, $b) {
    $sca = $a['rating'];
    $scb = $b['rating'];
    $na = $a['reviews'];
    $nb = $b['reviews'];
    if ($na < 5 || $nb < 5) {
      // <5 = sort to bottom
      if ($na == $nb) {
        return 0;
      }
      return ($na > $nb) ? -1 : 1;
    }
    if ($sca == $scb) {
      if ($na == $nb) {
        return 0;
      }
      return ($na > $nb) ? -1 : 1;
    }
    return ($sca > $scb) ? -1 : 1;
  }

}
