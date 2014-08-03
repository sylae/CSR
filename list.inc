<?php

/**
 * Includes for list-top.php
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
$pts = array(
  B => 5,
  G => 4,
  A => 3,
  S => 2,
  P => 1,
);

function isTopScenario($r) {
  if ($r['sum'] == 0) {
    return false;
  }
  return ($r[B] / $r['sum'] >= 0.3);
}

function isQualScenario($r) {
  if ($r['sum'] == 0) {
    return false;
  }
  return (($r[B] + $r[G]) / $r['sum'] >= 0.75);
}

function isWorthScenario($r) {
  if ($r['sum'] == 0) {
    return false;
  }
  return (($r[B] + $r[G]) / $r['sum'] >= 0.3);
}

function byScore($a, $b) {
  $sca = $a['rating'];
  $scb = $b['rating'];
  if ($sca == $scb) {
    return 0;
  }
  return ($sca > $scb) ? -1 : 1;
}

function byReviews($a, $b) {
  $sca = $a['reviews'];
  $scb = $b['reviews'];
  if ($sca == $scb) {
    return 0;
  }
  return ($sca < $scb) ? -1 : 1;
}

function generateList($list, $type = "reviews") {
  global $config;
  $l = "[list]" . PHP_EOL;
  foreach ($list as $e) {
    switch ($type) {
      case "reviews":
        $l .= "[*][url=".$config['ipbURL']."/topic/" .
          $e['tid'] . "-/]" . $e['title'] . "[/url] [i][size=3]([b]" .
          number_format($e['rating'], 1) . "[/b] with " . $e['reviews'] . " reviews)[/size][/i]" .
          PHP_EOL;
        break;
      case "ratings":
        $left = 5 - $e['reviews'];
        $l .= "[*][url=".$config['ipbURL']."/topic/" .
          $e['tid'] . "-/]" . $e['title'] . "[/url] [i][size=3]([b]" .
          $left . "[/b] " . (($left == 1) ? "review" : "reviews") . " needed)[/size][/i]" .
          PHP_EOL;
        break;
    }
  }

  return $l . "[/list]";
}

function head($t, $d) {
  return PHP_EOL . PHP_EOL . "[size=5][b]" . $t . "[/b][/size] [size=3]" . $d . "[/size]" . PHP_EOL;
}

function bestList($bgasp) {
  $list = array(
    'top' => isTopScenario($bgasp),
    'qual' => isQualScenario($bgasp),
    'worth' => isWorthScenario($bgasp),
  );
  if ($bgasp['sum'] < 5) {
    return 'short';
  }
  if ($list['top']) {
    return 'top';
  }
  if ($list['qual']) {
    return 'qual';
  }
  if ($list['worth']) {
    return 'worth';
  }
  return false;
}