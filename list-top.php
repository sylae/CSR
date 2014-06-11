<?php

/**
 * Description here
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require 'classes.php';

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

function bestList($bgasp) {
  $bgasp['sum'] = array_sum($bgasp);
  $list = array(
    'top' => isTopScenario($bgasp),
    'qual' => isQualScenario($bgasp),
    'worth' => isWorthScenario($bgasp),
  );
  if ($list['top']) return 'top';
  if ($list['qual']) return 'qual';
  if ($list['worth']) return 'worth';
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
);

while (($resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
  $bgasp = array(
    B => (int) $resu['b'],
    G => (int) $resu['g'],
    A => (int) $resu['a'],
    S => (int) $resu['s'],
    P => (int) $resu['p'],
  );
  $list = bestList($bgasp);
  if ($list) {
    $lists[$list][$resu['tid']] = $resu['title'];
  }
}
print_r($lists);