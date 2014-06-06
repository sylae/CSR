<?php

/**
 * Populates the database with scenario data. The only class that should
 * be doing INSERTs or DELETEs.
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class scenPoll extends CSR {

  protected $tid;
  protected $title;
  protected $tags;
  private $sw = "http://spiderwebforums.ipbhost.com/index.php?/topic/%s-/";
  private $html = false;
  private $map = array(
    'Best' => B,
    'Good' => G,
    'Average' => A,
    'Substandard' => S,
    'Poor' => P,
  );

  /**
   * Constructor
   * 
   * @global array $config Configuration Array
   * @param int $tid Topic ID to poll
   */
  function __construct($tid) {
    parent::__construct();
    $this->tid = $tid;
    $this->html();

    $this->wipeDB();
  }

  function getTitle() {
    $this->title = htmlqp($this->html, '.ipsType_pagetitle')->text();

    $query = 'INSERT INTO topic (tid, title) VALUES ('
      . $this->db->quote($this->tid, 'integer') . ', '
      . $this->db->quote($this->title, 'text') . ')';
    $this->db->exec($query);
  }

  function wipeDB() {
    // topic
    $query = 'DELETE FROM topic WHERE tid='
      . $this->db->quote($this->tid, 'integer') . ';';
    $this->db->exec($query);
    // tags
    $query = 'DELETE FROM tag WHERE tid='
      . $this->db->quote($this->tid, 'integer') . ';';
    $this->db->exec($query);
    // post
    $query = 'DELETE FROM post WHERE tid='
      . $this->db->quote($this->tid, 'integer') . ';';
    $this->db->exec($query);
  }

  function html() {
    if ($this->html) {
      return false;
    }

    $this->html = file_get_contents(sprintf($this->sw, $this->tid));
  }

  /**
   * Compare to strings and return how similar they are.
   * 
   * @url http://stackoverflow.com/a/5430851
   * @param string $str_a String to compare
   * @param string $str_b String to compare
   * @return float confidence, higher is better
   */
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

  function _score($i) {
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

  /**
   * Scan for ratings and insert them into the db
   */
  function getRatings() {
    $postdata = array();
    $this->html();
    var_dump($this->html);
    foreach (htmlqp($this->html, '#csr-rating') as $item) {
      $score = $this->_score($item->attr("data-csr-rating"));
      if ($c = preg_match_all("/entry(\\d+)/is", $item->closest('.post_block')->children('a')->attr('id'), $matches)) {
        $c1 = $matches[1][0];
      }
      $postdata[] = array($c1, $this->tid, $score);
    }
    $sth = $this->db->prepare('INSERT INTO post (pid, tid, rating) VALUES (?, ?, ?)');
    $this->db->extended->executeMultiple($sth, $postdata);
  }

  function getTags() {
    $alldata = array();
    foreach (htmlqp($this->html, 'a.ipsTag') as $item) {
      $t = trim(strtolower($item->text()));
      $alldata[] = array($this->tid, $t);
    }

    $sth = $this->db->prepare('INSERT INTO tags (tid, tag) VALUES (?, ?)');
    $this->db->extended->executeMultiple($sth, $alldata);
  }

}
