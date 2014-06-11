<?php

/**
 * Generates and updates the List of Scenarios
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require 'classes.php';

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

function bestList($bgasp) {
  $list = array(
    'top' => isTopScenario($bgasp),
    'qual' => isQualScenario($bgasp),
    'worth' => isWorthScenario($bgasp),
  );
  if ($bgasp['sum'] < 6)
    return 'short';
  if ($list['top'])
    return 'top';
  if ($list['qual'])
    return 'qual';
  if ($list['worth'])
    return 'worth';
  return false;
}

$db = & MDB2::singleton($config['db']);
if (PEAR::isError($db)) {
  die($db->getMessage());
}
$db->loadModule('Extended', null, false);

$query = 'SELECT * FROM composite;';
$res = & $db->query($query);

$lists = array(
  'top' => array(),
  'qual' => array(),
  'worth' => array(),
  'short' => array(),
);

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
  $bgasp['sum'] = array_sum($bgasp);
  if ($bgasp['sum'] == 0) {
    $csr = 0;
  } else {
    $csr = $val / $bgasp['sum'];
  }
  $list = bestList($bgasp);
  if ($list) {
    $lists[$list][$resu['tid']] = array(
      'title' => $resu['title'],
      'rating' => $csr,
      'reviews' => $bgasp['sum'],
    );
  }
}


// sort the lists
usort($lists['top'], "byScore");
usort($lists['qual'], "byScore");
usort($lists['worth'], "byScore");
usort($lists['short'], "byReviews");
