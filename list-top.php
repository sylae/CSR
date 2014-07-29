<?php

/**
 * Generates and updates the List of Scenarios
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require 'classes.php';
require 'list-top.inc';

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

$tid = 20609;

foreach (htmlqp(file_get_contents($config['ipbURL'].'/topic/' . $tid . '-/'), '#replyNumContainer') as $item) {
  if ($item->attr("data-reply-num") == 1) {
    $pid = $item->attr("data-pid");
    $fid = $item->attr("data-fid");
  }
}

$edit = new IPB($tid, $fid, $pid);
$edit->csrAll($l);
