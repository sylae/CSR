<?php

require 'config.php';
require 'qp.php';
require 'functions.php';

$test = array();

$text[] = 'Good';
$text[] = 'Average';
$text[] = 'Poor';
$text[] = 'Substandard';
$text[] = 'Best';
$text[] = 'best';
$text[] = 'best!';
$text[] = 'sub';
$text[] = 'Substd';
$text[] = 'worst';
$text[] = 'Bad';
$text[] = '<b>Good</b>';
$text[] = '     Average           ';
$text[] = "I really don't like Dallerdin's Scenario. It's nowhere near the best that BoA has to offer. Not the best at all. I rate it bad.";

foreach ($text as $key => $value) {
  $score = get_score($value);
  echo "$value = $score".PHP_EOL;
}