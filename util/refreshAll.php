<?php

/**
 * Purge and re-parse the entire CSR. Not to be used casually
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require __DIR__.'/../classes.php';
ensure_console();

$db = & MDB2::singleton($config['db']);
if (PEAR::isError($db)) {
  die($db->getMessage());
}
$db->loadModule('Extended', null, false);

$query = 'SELECT * FROM topic;';
$res = & $db->query($query);
$r = $res->numRows();
$rec = 1;
while (($resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
  echo 'Beginning scan for tid '.$resu['tid'].PHP_EOL;
  $scen = $resu['tid'];
  $poll = new scenPoll($scen);
  $csr = new scenario($scen);
  $csr->updateOP();
  
  $pct = number_format(($rec/$r)*100, 1);
  echo 'Sleeping... '.$pct."% complete.".PHP_EOL;
  $rec++;
  sleep(2);
}