<?php

/**
 * Populates the database with scenario data. The only class that should
 * be doing INSERTs or DELETEs.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class scenPoll extends CSR {

  /**
   * Topic ID / Scenario ID
   * @var int
   */
  protected $tid;

  /**
   * Scenario title
   * @var string
   */
  protected $title;

  /**
   * Any tags given to the scenario
   * @var array
   */
  protected $tags;

  /**
   * Raw HTML pulled from SW
   * @var string
   */
  private $html = false;

  /**
   * Constructor
   * 
   * @param int $tid Topic ID to poll
   */
  function __construct($tid) {
    parent::__construct();
    $this->tid = $tid;
    $this->html();

    $this->wipeDB();
    $this->getTitle();
    $this->getRatings();
    $this->getTags();
  }

  /**
   * Grab the title of the scenario from the HTML
   */
  function getTitle() {
    $this->title = htmlqp($this->html, '.ipsType_pagetitle')->text();

    $query = 'INSERT INTO topic (tid, title) VALUES ('
      . $this->db->quote($this->tid, 'integer') . ', '
      . $this->db->quote($this->title, 'text') . ')';
    $this->db->exec($query);

    $this->l('Scenario: ' . $this->title);
  }

  /**
   * Wipe the database of any existing data
   * 
   * This prevents issues such as tags being removed. If we left
   * the old data in and overwrite, we'd have a tag just laying
   * around still.
   */
  function wipeDB() {
    $this->l('Wiping all data for tid ' . $this->tid);
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

  /**
   * Pull the HTML from SW
   * 
   * @return boolean false if the HTML has already been grabbed.
   */
  function html() {
    if ($this->html) {
      return false;
    }

    $this->html = file_get_contents(sprintf($this->config['ipbURL']."/topic/%s-/", $this->tid));
    $this->l('HTTP rec\'d for tid ' . $this->tid);
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

  /**
   * Figure out the "score" meant by a given word.
   * We do this so that silly typos don't destroy all our hard work.
   * 
   * @param string $i String to check
   * @return int The score detected, given as a BGASP const
   */
  private function _score($i) {
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
    foreach ($this->map as $k => $v) {
      if ($k == $c) {
        return $v;
      }
    }
  }

  /**
   * Scan for ratings and insert them into the db
   */
  function getRatings() {
    $postdata = array();
    $this->html();
    foreach (htmlqp($this->html, '#csr-rating') as $item) {
      $score = $this->_score($item->attr("data-csr-rating"));
      $matches = array();
      if (preg_match_all("/entry(\\d+)/is", $item->closest('.post_block')->children('a')->attr('id'), $matches)) {
        $c1 = $matches[1][0];
      }
      $postdata[] = array($c1, $this->tid, $score);
      $this->l('Found review: ' . $c1 . ' with rating ' . $score);
    }
    $sth = $this->db->prepare('INSERT INTO post (pid, tid, rating) VALUES (?, ?, ?)');
    $this->db->extended->executeMultiple($sth, $postdata);
  }

  /**
   * Scan for tags and insert them into the db
   */
  function getTags() {
    $alldata = array();
    foreach (htmlqp($this->html, 'a.ipsTag') as $item) {
      $t = trim(strtolower($item->text()));
      $alldata[] = array($this->tid, $t);
      $this->l('Found tag: ' . $t);
    }

    $sth = $this->db->prepare('INSERT INTO tags (tid, tag) VALUES (?, ?)');
    $this->db->extended->executeMultiple($sth, $alldata);
  }

}
