<?php

require 'qp.php';
require 'functions.php';

$arg = $argv[1];

$url = "http://spiderwebforums.ipbhost.com/index.php?/topic/$arg-/";
$html = file_get_contents($url);

if ($c=preg_match_all("/http:\\/\\/spiderwebforums\\.ipbhost\\.com\\/index\\.php\\?\\/topic\\/(\\d+)-/is", $url, $matches)) {
  $c1=$matches[1][0];
  $tid = $c1;
}
$title = htmlqp($html, '.ipsType_pagetitle')->text();

echo "CSR review for $title (tid $tid)".PHP_EOL;

$tags = array();
$tagstr = "";
foreach (htmlqp($html, 'a.ipsTag') as $item) {
  $t = trim(strtolower($item->text()));
  $tags[] = $t;
  $tagstr .= $t.", ";
}

echo "Tags discovered: ".$tagstr.PHP_EOL.PHP_EOL;
$scores = array();
$bgasp = array(
  'Best' => 0,
  'Good' => 0,
  'Average' => 0,
  'Substandard' => 0,
  'Poor' => 0,
);
foreach (htmlqp($html, '#csr-rating') as $item) {
  $score = get_score($item->attr("data-csr-rating"));
  if ($c=preg_match_all("/entry(\\d+)/is", $item->closest('.post_block')->children('a')->attr('id'), $matches)) {
    $c1=$matches[1][0];
  }
  $scores[$c1] = $score;
  $bgasp[$score]++;
  echo "Rating from pid $c1: $score".PHP_EOL;
}

echo PHP_EOL.PHP_EOL.bgasp($bgasp);