<?php

/**
 * Generates and updates the List of Scenarios by Author
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require __DIR__.'/../classes.php';
require 'list.inc';
ensure_console();

$db = & MDB2::singleton($config['db']);
if (PEAR::isError($db)) {
  die($db->getMessage());
}
$db->loadModule('Extended', null, false);

$query = 'SELECT * FROM composite;';
$res = & $db->query($query);

$s = array();
$l = array();
while (($resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
  $s[$resu['tid']] = $resu['title'];
  $authors_array = explode(", ", $resu['author']);
  foreach ($authors_array as $author) {
    if (array_key_exists($author, $l)) {
      $l[$author][] = $resu['tid'];
    } else {
      $l[$author] = array();
      $l[$author][] = $resu['tid'];
    }
  }
}
ksort($l, SORT_NATURAL | SORT_FLAG_CASE);

$t = '';

foreach ($l as $name => $scens) {
  $t .= '[b]'.$name.'[/b]'.PHP_EOL.'[list]'.PHP_EOL;
  $author_scen = array();
  foreach ($scens as $id) {
    $author_scen[$id] = $s[$id];
  }
  asort($author_scen, SORT_NATURAL | SORT_FLAG_CASE);
  foreach ($author_scen as $id => $name) {
    $t .= "[*][url=".$config['ipbURL']."/topic/" .
          $id. "-/]" . $name . "[/url]".PHP_EOL;
  }
  $t.= '[/list]'.PHP_EOL;
}

$t.= '[encouragenecro]'.PHP_EOL;

foreach (htmlqp(file_get_contents($config['ipbURL'].'/topic/' . $config['topics']['auth'] . '-/'), '#replyNumContainer') as $item) {
  if ($item->attr("data-reply-num") == 1) {
    $pid = $item->attr("data-pid");
    $fid = $item->attr("data-fid");
  }
}

$edit = new IPB($tid, $fid, $pid);
$edit->csrAll($t);
