<?php

// http://stackoverflow.com/a/5430851
function string_compare($str_a, $str_b) {
  $length = strlen($str_a);
  $length_b = strlen($str_b);

  $i = 0;
  $segmentcount = 0;
  $segmentsinfo = array();
  $segment = '';
  while ($i < $length) {
    $char = substr($str_a, $i, 1);
    if (strpos($str_b, $char) !== FALSE) {               
      $segment = $segment.$char;
      if (strpos($str_b, $segment) !== FALSE) {
        $segmentpos_a = $i - strlen($segment) + 1;
        $segmentpos_b = strpos($str_b, $segment);
        $positiondiff = abs($segmentpos_a - $segmentpos_b);
        $posfactor = ($length - $positiondiff) / $length_b; // <-- ?
        $lengthfactor = strlen($segment)/$length;
        $segmentsinfo[$segmentcount] = array( 'segment' => $segment, 'score' => ($posfactor * $lengthfactor));
      } else {
        $segment = '';
        $i--;
        $segmentcount++;
      } 
    } else {
      $segment = '';
      $segmentcount++;
    }
    $i++;
  }   

  // PHP 5.3 lambda in array_map      
  $totalscore = array_sum(array_map(function($v) { return $v['score'];  }, $segmentsinfo));
  return $totalscore;
}

function get_score($i) {
  $scores = array(
    'Best' => null,
    'Good' => null,
    'Average' => null,
    'Substandard' => null,
    'Poor' => null,
  );
  foreach ($scores as $key => $score) {
    $scores[$key] = string_compare($key, ucfirst(strtolower(trim(strip_tags($i)))));
  }
  arsort($scores);
  $s = reset($scores);
  $c = array_search($s, $scores);
  return $c;
}

function bgasp($input) {
  $val = 0;
  $pct = array();
  $tot = array_sum($input);
  
  $pts = array(
    'Best' => 5,
    'Good' => 4,
    'Average' => 3,
    'Substandard' => 2,
    'Poor' => 1,
  );
  foreach ($input as $cat => $num) {
    $pct[$cat] = $num/$tot;
    $val += $num * $pts[$cat];
  }
  $csr = $val/$tot;
  $ret = "Composite Score: ".number_format($csr,1)."/5.0".PHP_EOL.PHP_EOL;
    foreach ($input as $cat => $num) {
      $ret .= $cat.": ".number_format($pct[$cat]*100,2)."% (".$num."/".$tot.")".PHP_EOL;
  }
  return $ret;
}