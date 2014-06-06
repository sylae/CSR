<?php

/**
 * Handles all scenario data
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class scenario {

  public $tid;
  public $title;
  public $tags;
  public $bgasp;
  private $config;
  private $db;
  private $url;
  private $sw = "http://spiderwebforums.ipbhost.com/index.php?/topic/%s-/";
  private $html = false;
  private $map = array(
    'Best' => B,
    'Good' => G,
    'Average' => A,
    'Substandard' => S,
    'Poor' => P,
  );

  function __construct($tid) {
    global $config;
    $this->tid = $tid;
    $this->config = $config;
    $this->url = sprintf($this->sw, $this->tid);
    $this->_setDB();

    // Load or parse topic information
    $this->loadMeta();
  }

  function _setDB() {
    $this->db = & MDB2::singleton($this->config['db']);
    if (PEAR::isError($this->db)) {
      die($this->db->getMessage());
    }
    $this->db->loadModule('Extended', null, false);
  }

  function loadMeta() {
    // Check database
    $query = 'SELECT * FROM composite WHERE tid='
      . $this->db->quote($this->tid, 'integer') . ';';
    $res = & $this->db->query($query);
    if ($res->numRows() > 0) {
      $resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
      $bgasp = array(
        B => (int) $resu['b'],
        G => (int) $resu['g'],
        A => (int) $resu['a'],
        S => (int) $resu['s'],
        P => (int) $resu['p'],
      );

      // build tags
      $query = 'SELECT * FROM tags WHERE tid='
        . $this->db->quote($this->tid, 'integer') . ';';
      $tag = & $this->db->queryCol($query, null, 'tag');
      $this->title = $resu['title'];
      $this->tags = $tag;
      $this->bgasp = $bgasp;
    } else {
      // Okay, create it. Let's grab the HTML and parse the title.
      $this->html();
      $this->title = htmlqp($this->html, '.ipsType_pagetitle')->text();

      $query = 'INSERT INTO topic (tid, title) VALUES ('
        . $this->db->quote($this->tid, 'integer') . ', '
        . $this->db->quote($this->title, 'text') . ')';
      $res = & $this->db->exec($query);
      
      // We'll also fetch info
    }
  }

  function html() {
    if ($this->html) {
      return false;
    }

    $this->html = file_get_contents($this->url);
  }

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
        $segment = $segment . $char;
        if (strpos($str_b, $segment) !== FALSE) {
          $segmentpos_a = $i - strlen($segment) + 1;
          $segmentpos_b = strpos($str_b, $segment);
          $positiondiff = abs($segmentpos_a - $segmentpos_b);
          $posfactor = ($length - $positiondiff) / $length_b; // <-- ?
          $lengthfactor = strlen($segment) / $length;
          $segmentsinfo[$segmentcount] = array('segment' => $segment, 'score' => ($posfactor * $lengthfactor));
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
    $totalscore = array_sum(array_map(function($v) {
        return $v['score'];
      }, $segmentsinfo));
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
      $scores[$key] = $this->string_compare($key, ucfirst(strtolower(trim(strip_tags($i)))));
    }
    arsort($scores);
    $s = reset($scores);
    $c = array_search($s, $scores);
    return $this->strToConst($c);
  }

  function strToConst($str) {
    foreach ($this->map as $k => $v) {
      if ($k == $str)
        return $v;
    }
  }

  function ConstToStr($c) {
    foreach ($this->map as $k => $v) {
      if ($v == $c)
        return $k;
    }
  }

  function bgaspBB() {
    $val = 0;
    $pct = array();
    $tot = array_sum($this->bgasp);

    $pts = array(
      B => 5,
      G => 4,
      A => 3,
      S => 2,
      P => 1,
    );
    foreach ($this->bgasp as $cat => $num) {
      $pct[$cat] = $num / $tot;
      $val += $num * $pts[$cat];
    }
    $csr = $val / $tot;
    $ret = "Composite Score: [b]" . number_format($csr, 1) . "[/b]/5.0" . PHP_EOL . PHP_EOL;
    foreach ($this->bgasp as $cat => $num) {
      $ret .= $this->ConstToStr($cat) . ": [b]" . number_format($pct[$cat] * 100, 2) . "%[/b] [i](" . $num . "/" . $tot . ")[/i]" . PHP_EOL;
    }
    return $ret;
  }

  function isTopScenario($r) {
    return ($r[B] / $r['sum'] >= 0.3);
  }

  function isQualScenario($r) {
    return (($r[B] + $r[G]) / $r['sum'] >= 0.75);
  }

  function isWorthScenario($r) {
    return (($r[B] + $r[G]) / $r['sum'] >= 0.3);
  }

  /**
   * Scan for ratings and insert them into the db
   */
  function getRatings() {
    $postdata = array();
    $scores = array();
    $bgasp = array(
      B => 0,
      G => 0,
      A => 0,
      S => 0,
      P => 0,
    );
    $this->html();
    var_dump($this->html);
    foreach (htmlqp($this->html, '#csr-rating') as $item) {
      $score = $this->get_score($item->attr("data-csr-rating"));
      if ($c = preg_match_all("/entry(\\d+)/is", $item->closest('.post_block')->children('a')->attr('id'), $matches)) {
        $c1 = $matches[1][0];
      }
      $scores[$c1] = $score;
      $bgasp[$score] ++;
      $postdata[] = array($c1, $this->tid, $score);
    }
    //$sth = $db->prepare('INSERT INTO post (pid, tid, rating) VALUES (?, ?, ?)');
    //$db->extended->executeMultiple($sth, $postdata);
  }
  
  /**
   * Generate a [composite] BBcode tag ready for injections into the post
   * 
   * @return string Payload ready for insertion
   */
  function getPostPayload() {
    $data = array(
      'title' => $this->title,
      'tid' => $this->tid,
      'tags' => $this->tags,
      'bgasp' => $this->bgasp,
    );
    $payload = base64_encode(json_encode($data));
    return '[composite=' . $payload . ']' . PHP_EOL . $this->bgaspBB() . PHP_EOL . '[/composite]';
  }

}
