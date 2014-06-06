<?php
// Probably won't compile. This is just stuff I still have to put into
// the new scenario class


$tags = array();
$alldata = array();
foreach (htmlqp($html, 'a.ipsTag') as $item) {
  $t = trim(strtolower($item->text()));
  $tags[] = $t;
  $alldata[] = array($tid, $t);
}
$tagstr = implode(", ", $tags);

// remove all existing tags if applicable
$query = 'DELETE FROM tag WHERE tid='
  . $db->quote($tid, 'integer') .';';
$res =& $db->exec($query);

$sth = $db->prepare('INSERT INTO tags (tid, tag) VALUES (?, ?)');
$db->extended->executeMultiple($sth, $alldata);

echo "Tags discovered: ".$tagstr.PHP_EOL.PHP_EOL;
$scores = array();
$bgasp = array(
    B => 0,
    G => 0,
    A => 0,
    S => 0,
    P => 0,
);

// remove all existing reviews for this scenario
$query = 'DELETE FROM post WHERE tid='
  . $db->quote($tid, 'integer') .';';
$res =& $db->exec($query);