<?php

require 'config.php';
require 'qp.php';
require 'functions.php';
require 'MDB2.php';

$db =& MDB2::connect($config['db']);
if (PEAR::isError($db)) {
  die($db->getMessage());
}
$db->loadModule('Extended', null, false);

$arg = $argv[1];

$url = "http://spiderwebforums.ipbhost.com/index.php?/topic/$arg-/";
$html = file_get_contents($url);

if ($c=preg_match_all("/http:\\/\\/spiderwebforums\\.ipbhost\\.com\\/index\\.php\\?\\/topic\\/(\\d+)-/is", $url, $matches)) {
  $c1=$matches[1][0];
  $tid = $c1;
}
$title = htmlqp($html, '.ipsType_pagetitle')->text();

echo "CSR review for $title (tid $tid)".PHP_EOL;

// Check database
$query = 'SELECT * FROM topic WHERE tid='
    . $db->quote($tid,   'integer')   .';';
$res =& $db->query($query);

if ($res->numRows() == 0) {
  // Okay, create it
  $query = 'INSERT INTO topic (tid, title) VALUES ('
    . $db->quote($tid, 'integer') .', '
    . $db->quote($title, 'text') .')';
  $res =& $db->exec($query);
}

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

$postdata = array();
foreach (htmlqp($html, '#csr-rating') as $item) {
  $score = get_score($item->attr("data-csr-rating"));
  if ($c=preg_match_all("/entry(\\d+)/is", $item->closest('.post_block')->children('a')->attr('id'), $matches)) {
    $c1=$matches[1][0];
  }
  $scores[$c1] = $score;
  $bgasp[$score]++;
  $postdata[] = array($c1, $tid, $score);
  echo "Rating from pid $c1: ".ConstToStr($score).PHP_EOL;
}
$sth = $db->prepare('INSERT INTO post (pid, tid, rating) VALUES (?, ?, ?)');
$db->extended->executeMultiple($sth, $postdata);

echo PHP_EOL.PHP_EOL.bgasp($bgasp);

$db->disconnect();