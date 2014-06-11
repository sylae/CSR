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

function generateList($list, $type = "reviews") {
  $l = "[list]" . PHP_EOL;
  foreach ($list as $e) {
    switch ($type) {
      case "reviews":
        $l .= "[*][url=http://spiderwebforums.ipbhost.com/index.php?/topic/" .
          $e['tid'] . "-/]" . $e['title'] . "[/url] [i][size=3]([b]" .
          number_format($e['rating'], 1) . "[/b] with " . $e['reviews'] . " reviews)[/size][/i]" .
          PHP_EOL;
        break;
      case "ratings":
        $left = 5 - $e['reviews'];
        $l .= "[*][url=http://spiderwebforums.ipbhost.com/index.php?/topic/" .
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
  if ($bgasp['sum'] < 5)
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
    $lists[$list][] = array(
      'tid' => $resu['tid'],
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

$l = head("Top Scenarios", 'At least 30% of the reviews have rated "Best"');
$l .= generateList($lists['top']);
$l .= head("Quality Scenarios", 'At least 75% of the reviews have rated "Good" or higher.');
$l .= generateList($lists['qual']);
$l .= head("Worthwhile Scenarios", 'At least 30% of the reviews rated "Good" or higher.');
$l .= generateList($lists['worth']);
$l .= head("Unranked Scenarios", 'Less than 5 CSR reviews.');
$l .= generateList($lists['short'], "ratings");

$tid = 12213;

foreach (htmlqp(file_get_contents('http://spiderwebforums.ipbhost.com/index.php?/topic/' . $tid . '-/'), '#replyNumContainer') as $item) {
  if ($item->attr("data-reply-num") == 1) {
    $pid = $item->attr("data-pid");
    $fid = $item->attr("data-fid");
  }
}

$edit = new IPB($tid, $fid, $pid);
$edit->csrAll($l);
